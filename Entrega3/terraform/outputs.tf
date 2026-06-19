output "region" {
  value       = var.region
  description = "Región de Google Cloud utilizada"
}

output "project_id" {
  value       = var.project_id
  description = "ID del proyecto en Google Cloud"
}

output "kubernetes_cluster_name" {
  value       = google_container_cluster.primary.name
  description = "Nombre del clúster de GKE"
}

output "kubernetes_cluster_host" {
  value       = google_container_cluster.primary.endpoint
  description = "Endpoint del clúster de GKE"
}

output "nginx_ingress_ip" {
  value       = google_compute_address.nginx_ingress_ip.address
  description = "IP estática reservada para el Ingress Nginx"
}

output "artifact_registry_repository_url" {
  value       = "${var.region}-docker.pkg.dev/${var.project_id}/${var.repository_id}"
  description = "URL para subir las imágenes Docker en Artifact Registry"
}
