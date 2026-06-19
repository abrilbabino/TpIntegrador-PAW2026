variable "project_id" {
  description = "El ID de tu proyecto en Google Cloud"
  type        = string
  default     = "project-f3583ede-db03-4872-95c"
}

variable "region" {
  description = "La región de Google Cloud"
  type        = string
  default     = "us-central1"
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
