#!/usr/bin/env bash
# ==============================================================================
# SUBIR SECRETOS A GCP SECRET MANAGER - PAWMAP
# ==============================================================================
#
# PRERREQUISITOS:
#   - gcloud CLI autenticado con permisos de admin
#   - jq instalado (brew install jq)
#
# USO: Ejecutar desde la carpeta Entrega3/ (o donde corresponda)
#   chmod +x subir-secretos.sh
#   ./subir-secretos.sh
#
# ==============================================================================

set -euo pipefail

RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
NC='\033[0m'

PROJECT="project-f3583ede-db03-4872-95c"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_FILE="${SCRIPT_DIR}/config.json"

if ! command -v jq &> /dev/null; then
    echo -e "${RED}No se encontró 'jq'. Instalalo con: brew install jq${NC}"
    exit 1
fi

if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}No se encontró config.json en la carpeta padre${NC}"
    exit 1
fi

SECRETS=(
    "DB_PASSWORD"
    "MAIL_PASS"
    "MERCADO_PAGO_ACCESS_TOKEN"
)

for SECRET_NAME in "${SECRETS[@]}"; do
    SECRET_VALUE=$(jq -r --arg key "$SECRET_NAME" '.[$key] // empty' "$CONFIG_FILE")

    if [ -z "$SECRET_VALUE" ]; then
        echo -e "${YELLOW}Advertencia: El valor para ${SECRET_NAME} está vacío en config.json, saltando...${NC}"
        continue
    fi

    # Crear el secreto en GCP si no existe
    echo "Creando secreto ${SECRET_NAME} en GCP..."
    gcloud secrets create "$SECRET_NAME" --replication-policy="automatic" --project="$PROJECT" --quiet 2>/dev/null || true

    # Agregar la versión al secreto
    echo "Subiendo valor para ${SECRET_NAME}..."
    printf '%s' "$SECRET_VALUE" | gcloud secrets versions add "$SECRET_NAME" --data-file=- --project="$PROJECT"
done

echo -e "${GREEN}¡Todos los secretos fueron subidos exitosamente!${NC}"