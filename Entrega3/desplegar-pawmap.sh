#!/usr/bin/env bash
# ==============================================================================
# SCRIPT DE DESPLIEGUE CONTROLADO - PAWMAP
# Despliegue de GKE + VPC + Artifact Registry + Manifiestos K8s
# ==============================================================================
#
# PRERREQUISITOS:
#   - gcloud CLI autenticado con permisos de admin
#   - Docker instalado y corriendo localmente (Docker Desktop en Mac)
#   - Terraform instalado (brew install terraform)
#
# USO: Ejecutar desde la carpeta Entrega3/
#   chmod +x desplegar-pawmap.sh
#   ./desplegar-pawmap.sh
#   ./desplegar-pawmap.sh --dry-run
#   ./desplegar-pawmap.sh --skip-confirmations
#   ./desplegar-pawmap.sh --dry-run --skip-confirmations
#
# ==============================================================================

set -euo pipefail

# --- Flags ---
SKIP_CONFIRMATIONS=false
DRY_RUN=false

for arg in "$@"; do
    case "$arg" in
        --skip-confirmations) SKIP_CONFIRMATIONS=true ;;
        --dry-run) DRY_RUN=true ;;
        *)
            echo "Argumento desconocido: $arg"
            echo "Uso: $0 [--dry-run] [--skip-confirmations]"
            exit 1
            ;;
    esac
done

# --- Configuración ---
GCP_PROJECT_ID="project-f3583ede-db03-4872-95c"
REGION="us-central1"
CLUSTER_NAME="pawmap-cluster"
REGISTRY="${REGION}-docker.pkg.dev/${GCP_PROJECT_ID}/pawmap-repo/pawmap-app"
DOMAIN="pawmap.lat"

# --- Rutas de directorios ---
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TERRAFORM_DIR="${SCRIPT_DIR}/terraform"
K8S_DIR="${SCRIPT_DIR}/k8s"

# --- Colores ---
CYAN='\033[0;36m'
WHITE='\033[1;37m'
GRAY='\033[0;37m'
DARKGRAY='\033[1;30m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
RED='\033[0;31m'
MAGENTA='\033[0;35m'
NC='\033[0m' # No Color

# --- Helpers ---
write_step() {
    local step="$1"
    local msg="$2"
    echo ""
    echo -e "${CYAN}===============================================================${NC}"
    echo -e "${CYAN}  ${step}${NC}"
    echo -e "${WHITE}  ${msg}${NC}"
    echo -e "${CYAN}===============================================================${NC}"
}

# Devuelve 0 (true) si se debe continuar, 1 (false) si el usuario dijo que no
confirm_step() {
    local msg="$1"
    if [ "$SKIP_CONFIRMATIONS" = false ]; then
        echo ""
        read -r -p "${msg} (s/n) " response
        if [ "$response" != "s" ] && [ "$response" != "S" ]; then
            echo -e "${YELLOW}Paso omitido por el usuario.${NC}"
            return 1
        fi
    fi
    return 0
}

# Llamar inmediatamente después de un comando para chequear su éxito
check_command_success() {
    local exit_code=$?
    if [ $exit_code -ne 0 ]; then
        echo -e "${RED}ERROR: El último comando falló con código ${exit_code}${NC}"
        echo -e "${YELLOW}Revisar el error y decidir si continuar.${NC}"
        if [ "$SKIP_CONFIRMATIONS" = false ]; then
            read -r -p "¿Continuar de todas formas? (s/n) " response
            if [ "$response" != "s" ] && [ "$response" != "S" ]; then
                echo -e "${RED}Despliegue abortado por el usuario.${NC}"
                exit 1
            fi
        fi
    fi
}

# ==============================================================================
echo ""
echo -e "${MAGENTA}  +============================================================+${NC}"
echo -e "${MAGENTA}  |              PAWMAP - Despliegue Automatizado              |${NC}"
echo -e "${MAGENTA}  |      GKE + VPC + Artifact Registry + K8s Probes + TLS      |${NC}"
echo -e "${MAGENTA}  +============================================================+${NC}"
echo ""

if [ "$DRY_RUN" = true ]; then
    echo -e "${YELLOW}  [MODO DRY-RUN] No se ejecutarán cambios destructivos ni despliegues.${NC}"
    echo ""
fi

# ==============================================================================
# PASO 0: Verificar prerrequisitos
# ==============================================================================
write_step "PASO 0/6" "Verificando prerrequisitos..."

echo -e "${GRAY}  Verificando gcloud...${NC}"
if ! command -v gcloud &> /dev/null; then
    echo -e "${RED}  [!] ERROR: gcloud no está instalado o no está en el PATH${NC}"
    exit 1
fi
set +e +o pipefail
GCLOUD_VER="$(gcloud --version 2>&1)"
GCLOUD_VER="${GCLOUD_VER%%$'\n'*}"
set -e -o pipefail
echo -e "${DARKGRAY}    ${GCLOUD_VER}${NC}"

echo -e "${GRAY}  Verificando kubectl...${NC}"
if ! command -v kubectl &> /dev/null; then
    echo -e "${RED}  [!] ERROR: kubectl no está instalado o no está en el PATH${NC}"
    exit 1
fi
set +e +o pipefail
KUBECTL_VER="$(kubectl version --client 2>&1)"
KUBECTL_VER="${KUBECTL_VER%%$'\n'*}"
set -e -o pipefail
echo -e "${DARKGRAY}    ${KUBECTL_VER}${NC}"

echo -e "${GRAY}  Verificando docker...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}  [!] ERROR: docker no está instalado o no está en el PATH${NC}"
    exit 1
fi
set +e +o pipefail
DOCKER_VER="$(docker version --format '{{.Client.Version}}' 2>&1)"
set -e -o pipefail
echo -e "${DARKGRAY}    Docker Client Version: ${DOCKER_VER}${NC}"

echo -e "${GRAY}  Verificando terraform/tofu...${NC}"
if command -v tofu &> /dev/null; then
    TF_CMD="tofu"
elif command -v terraform &> /dev/null; then
    TF_CMD="terraform"
else
    echo -e "${RED}  [!] ERROR: no se encontró 'tofu' ni 'terraform' en el PATH${NC}"
    exit 1
fi
set +e +o pipefail
TF_VER="$("$TF_CMD" version 2>&1)"
TF_VER="${TF_VER%%$'\n'*}"
set -e -o pipefail
echo -e "${DARKGRAY}    Usando: ${TF_CMD} -> ${TF_VER}${NC}"

echo ""
echo -e "${GREEN}  [OK] Todos los prerrequisitos verificados${NC}"

# ==============================================================================
# PASO 1: Subir Secretos a GCP Secret Manager
# ==============================================================================
write_step "PASO 1/6" "Subiendo secretos a GCP Secret Manager..."

if [ "$DRY_RUN" = true ]; then
    echo -e "${YELLOW}  [DRY-RUN] Se omitiría la carga de secretos.${NC}"
else
    SUBIR_SECRETOS_SCRIPT="${SCRIPT_DIR}/subir-secretos.sh"
    if [ -f "$SUBIR_SECRETOS_SCRIPT" ]; then
        echo -e "${GRAY}  Ejecutando script de subida de secretos...${NC}"
        bash "$SUBIR_SECRETOS_SCRIPT"
        check_command_success
    else
        echo -e "${RED}  [!] ERROR: No se encontró el script subir-secretos.sh en ${SCRIPT_DIR}${NC}"
        echo -e "${RED}Despliegue abortado debido a la falta de script de secretos.${NC}"
        exit 1
    fi
fi

# ==============================================================================
# PASO 2: Terraform Apply (Infraestructura de GKE + VPC + Artifact Registry)
# ==============================================================================
write_step "PASO 2/6" "Terraform: Creando infraestructura en GCP"

if [ "$DRY_RUN" = true ]; then
    echo -e "${YELLOW}  [DRY-RUN] Se omitiría terraform init/apply.${NC}"
else
    pushd "$TERRAFORM_DIR" > /dev/null

    echo -e "${GRAY}  Ejecutando ${TF_CMD} init...${NC}"
    "$TF_CMD" init -upgrade
    check_command_success

    echo ""
    echo -e "${GRAY}  Ejecutando ${TF_CMD} plan...${NC}"
    "$TF_CMD" plan -out=tfplan \
        -var="project_id=${GCP_PROJECT_ID}" \
        -var="region=${REGION}" \
        -var="cluster_name=${CLUSTER_NAME}"
    check_command_success

    if confirm_step "¿Aplicar los cambios de Terraform/Tofu para desplegar el clúster GKE?"; then
        echo ""
        echo -e "${GRAY}  Aplicando ${TF_CMD} (esto puede tardar 8-15 minutos)...${NC}"
        "$TF_CMD" apply tfplan
        check_command_success
        echo -e "${GREEN}  [OK] Infraestructura de GCP creada con éxito${NC}"
    fi
    popd > /dev/null
fi

# ==============================================================================
# PASO 3: Construir y Subir imagen Docker
# ==============================================================================
write_step "PASO 3/6" "Docker: Construir y subir la imagen al Artifact Registry"

if [ "$DRY_RUN" = true ]; then
    echo -e "${YELLOW}  [DRY-RUN] Se omitiría docker build y push.${NC}"
else
    # 1. Configurar credenciales del clúster kubectl
    echo -e "${GRAY}  Configurando credenciales de kubectl para el clúster ${CLUSTER_NAME}...${NC}"
    gcloud container clusters get-credentials "$CLUSTER_NAME" --region "$REGION" --project "$GCP_PROJECT_ID"
    check_command_success

    # 2. Autenticar Docker con Artifact Registry de GCP
    echo -e "${GRAY}  Autenticando Docker con Artifact Registry en la región ${REGION}...${NC}"
    gcloud auth configure-docker "${REGION}-docker.pkg.dev" --quiet
    check_command_success

    if confirm_step "¿Construir la imagen de Docker localmente y subirla a Artifact Registry?"; then
        pushd "$SCRIPT_DIR" > /dev/null

        echo -e "${GRAY}  Construyendo la imagen Docker para PawMap...${NC}"
        # Nota: en Apple Silicon (M1/M2) forzamos plataforma linux/amd64
        # para compatibilidad con los nodos de GKE.
        docker build --platform linux/amd64 -t "${REGISTRY}:latest" .
        check_command_success

        echo -e "${GRAY}  Subiendo la imagen al Artifact Registry...${NC}"
        docker push "${REGISTRY}:latest"
        check_command_success

        echo -e "${GREEN}  [OK] Imagen Docker subida exitosamente${NC}"
        popd > /dev/null
    fi
fi

# ==============================================================================
# PASO 4: Aplicar Manifiestos de Kubernetes
# ==============================================================================
write_step "PASO 4/6" "Kubernetes: Aplicando manifiestos"

if [ "$DRY_RUN" = true ]; then
    echo -e "${YELLOW}  [DRY-RUN] Se omitiría kubectl apply de los manifiestos.${NC}"
else
    if confirm_step "¿Aplicar los manifiestos de Kubernetes al clúster?"; then
        echo -e "${GRAY}  Aplicando ConfigMap de entorno...${NC}"
        kubectl apply -f "${K8S_DIR}/configmap.yaml"
        check_command_success

        echo -e "${GRAY}  Aplicando ExternalSecrets de GCP...${NC}"
        kubectl apply -f "${K8S_DIR}/external-secrets.yaml"
        check_command_success

        echo -e "${GRAY}  Aplicando Service...${NC}"
        kubectl apply -f "${K8S_DIR}/service.yaml"
        check_command_success

        echo -e "${GRAY}  Aplicando Deployment (e inyectando variables)...${NC}"
        kubectl apply -f "${K8S_DIR}/deployment.yaml"
        check_command_success

        echo -e "${GRAY}  Aplicando Ingress...${NC}"
        kubectl apply -f "${K8S_DIR}/ingress.yaml"
        check_command_success

        echo -e "${GRAY}  Aplicando Cert-Manager ClusterIssuer...${NC}"
        kubectl apply -f "${K8S_DIR}/cluster-issuer.yaml"
        check_command_success

        # Forzar reinicio de pods para garantizar que tomen la última versión subida
        echo -e "${GRAY}  Forzando rollout restart del deployment...${NC}"
        kubectl rollout restart deployment pawmap-app
        kubectl rollout status deployment pawmap-app --timeout=300s

        echo -e "${GREEN}  [OK] Manifiestos aplicados y pods iniciados${NC}"
    fi
fi

# ==============================================================================
# PASO 5: Configuración de DNS
# ==============================================================================
write_step "PASO 5/6" "Configuración y Verificación de IP del Ingress"

echo -e "${GRAY}  Esperando a que GCP asigne la IP externa del Ingress...${NC}"
TIMEOUT=180
ELAPSED=0
INGRESS_IP=""

while [ "$ELAPSED" -lt "$TIMEOUT" ] && [ -z "$INGRESS_IP" ]; do
    if [ "$DRY_RUN" = true ]; then
        INGRESS_IP="1.2.3.4"
        break
    fi
    INGRESS_IP=$(kubectl get ingress pawmap-ingress -o jsonpath='{.status.loadBalancer.ingress[0].ip}' 2>/dev/null || true)
    if [ -n "$INGRESS_IP" ]; then
        break
    fi
    sleep 10
    ELAPSED=$((ELAPSED + 10))
    echo -e "${DARKGRAY}    Esperando IP... (${ELAPSED}s)${NC}"
done

if [ -n "$INGRESS_IP" ]; then
    echo ""
    echo -e "${GREEN}  +---------------------------------------------+${NC}"
    echo -e "${GREEN}  |  NUEVA IP ASIGNADA: ${INGRESS_IP}${NC}"
    echo -e "${GREEN}  +---------------------------------------------+${NC}"
    echo ""
    echo -e "${YELLOW}  [!] ACCION MANUAL REQUERIDA - DNS${NC}"
    echo -e "${WHITE}  Configura un registro A en tu proveedor de DNS para el dominio:${NC}"
    echo -e "${GREEN}    ${DOMAIN}  ->  ${INGRESS_IP}${NC}"
    echo ""
    echo -e "${GRAY}  Nota: Let's Encrypt no podrá emitir el certificado TLS hasta${NC}"
    echo -e "${GRAY}  que el dominio apunte correctamente a esta IP.${NC}"
else
    echo -e "${YELLOW}  [!] Ingress no tiene IP asignada todavía. Consulta luego usando:${NC}"
    echo -e "${YELLOW}      kubectl get ingress pawmap-ingress${NC}"
fi

# ==============================================================================
# PASO 6: Verificación del Estado
# ==============================================================================
write_step "PASO 6/6" "Verificando el estado del clúster"

if [ "$DRY_RUN" = true ]; then
    echo -e "${YELLOW}  [DRY-RUN] Se omitiría la verificación.${NC}"
else
    echo ""
    echo -e "${CYAN}  --- Pods activos ---${NC}"
    kubectl get pods -l app=pawmap

    echo ""
    echo -e "${CYAN}  --- Estado de los Secretos Externos ---${NC}"
    kubectl get externalsecret pawmap-secrets-es
    kubectl get secret pawmap-secrets

    echo ""
    echo -e "${CYAN}  --- Estado de Certificados ---${NC}"
    kubectl get certificate
fi

# ==============================================================================
# RESUMEN FINAL
# ==============================================================================
echo ""
echo -e "${GREEN}  +============================================================+${NC}"
echo -e "${GREEN}  |                 DESPLIEGUE COMPLETADO                      |${NC}"
echo -e "${GREEN}  +============================================================+${NC}"
echo -e "${GREEN}  |                                                            |${NC}"
echo -e "${GREEN}  |  Dominio:    https://${DOMAIN}                        |${NC}"
echo -e "${GREEN}  |  Ingress:    nginx-ingress                                 |${NC}"
echo -e "${GREEN}  |  Imágenes:   GCP Artifact Registry                         |${NC}"
echo -e "${GREEN}  |  Config:     ConfigMap (pawmap-config)                     |${NC}"
echo -e "${GREEN}  |  Secretos:   GCP Secret Manager + ExternalSecrets          |${NC}"
echo -e "${GREEN}  |                                                            |${NC}"
echo -e "${GREEN}  +============================================================+${NC}"
echo ""