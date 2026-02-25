# -----------------------------------------------------------------------
# Variables — override via env vars or --set on the CLI
# e.g. FRANKENPHP_VERSION=1.12.0 docker buildx bake
# -----------------------------------------------------------------------

variable "FRANKENPHP_VERSION" {
  description = "FrankenPHP release version"
  default     = "1.11.2"
}

variable "PHP_VERSION" {
  description = "PHP release version"
  default     = "8.5.3"
}

variable "PUSH" {
  default = "0"
}

variable "REGISTRY" {
  default = ""
}

# Derived — prepends registry if set, otherwise just the image name
variable "IMAGE" {
  default = REGISTRY != "" ? "${REGISTRY}/aloft" : "tempestphp/aloft"
}

# Derived values — not meant to be overridden directly
variable "BASE_IMAGE" {
  default = "dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION}"
}

variable "VERSION_TAG" {
  default = "${FRANKENPHP_VERSION}-${PHP_VERSION}"
}

# -----------------------------------------------------------------------
# Shared platform target — all runner targets inherit from this
# -----------------------------------------------------------------------

target "_common" {
  dockerfile = "Dockerfile"
  context    = "."
  platforms  = PUSH == "1" ? ["linux/amd64", "linux/arm64"] : []
  output     = PUSH == "1" ? ["type=registry"] : ["type=docker"]
  args = {
    FRANKENPHP_VERSION = FRANKENPHP_VERSION
    PHP_VERSION        = PHP_VERSION
    BASE_IMAGE         = BASE_IMAGE
  }
}

# -----------------------------------------------------------------------
# latest-nonroot — runner is gcr.io/distroless/cc-debian13:nonroot
# Tags: tempestphp/aloft:latest-nonroot
#       tempestphp/aloft:1.11.2-8.5.3-nonroot
# -----------------------------------------------------------------------

target "latest-nonroot" {
  inherits = ["_common"]
  target   = "common"
  args = {
    DISTROLESS_VARIANT = "nonroot"
  }
  tags = [
    "${IMAGE}:latest-nonroot",
    "${IMAGE}:${VERSION_TAG}-nonroot",
  ]
}

# -----------------------------------------------------------------------
# debug-nonroot — runner is gcr.io/distroless/cc-debian13:debug-nonroot
# Includes busybox shell for exec access while still running as nonroot.
# Tags: tempestphp/aloft:debug-nonroot
#       tempestphp/aloft:1.11.2-8.5.3-debug-nonroot
# -----------------------------------------------------------------------

target "debug-nonroot" {
  inherits = ["_common"]
  target   = "common"
  args = {
    DISTROLESS_VARIANT = "debug-nonroot"
  }
  tags = [
    "${IMAGE}:debug-nonroot",
    "${IMAGE}:${VERSION_TAG}-debug-nonroot",
  ]
}

# -----------------------------------------------------------------------
# Default group — builds both variants in parallel
# -----------------------------------------------------------------------

group "default" {
  targets = ["latest-nonroot", "debug-nonroot"]
}
