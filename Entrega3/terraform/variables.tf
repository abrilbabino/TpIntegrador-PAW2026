variable "project_id" {
  description = "El ID de tu proyecto en Google Cloud"
  type        = string
  default     = "project-f7af048a-a298-4d12-b7e"
}

variable "region" {
  description = "La región de Google Cloud"
  type        = string
  default     = "us-east1"
}


variable "cluster_name" {
  description = "Nombre del clúster de GKE"
  type        = string
  default     = "pawmap-cluster"
}


variable "repository_id" {
  description = "Nombre del repositorio en GCP Artifact Registry"
  type        = string
  default     = "pawmap-repo"
}



variable "domain" {
  description = "Domain name for TLS and Ingress"
  type        = string
  default     = "ideafy.lat"
}

variable "letsencrypt_email" {
  description = "Email for Let's Encrypt certificate notifications"
  type        = string
  default     = "abril.nadia.babino984@gmail.com"
}
