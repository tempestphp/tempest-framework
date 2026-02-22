# Build-time arguments — set by bake.hcl, can be overridden individually
ARG FRANKENPHP_VERSION=1.11.2
ARG PHP_VERSION=8.5.3
ARG BASE_IMAGE=dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION}
# Controls which distroless variant is used as the runner base.
# Valid values mirror gcr.io/distroless/cc-debian13 tags: nonroot | debug-nonroot
ARG DISTROLESS_VARIANT=nonroot

FROM ${BASE_IMAGE} AS frankenphp

RUN curl -sSLf \
		-o /usr/local/bin/install-php-extensions \
		https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
	chmod +x /usr/local/bin/install-php-extensions

# Install additional extensions here and they are carried forward into the final image
RUN install-php-extensions \
	gd \
    intl \
    mysqli \
    pcntl \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    redis \
    zip

# Install pax-utils for lddtree
RUN apt-get update && apt-get install -y --no-install-recommends pax-utils && rm -rf /var/lib/apt/lists/*

# Copy and run the staging script which collects all runtime files into
# /tmp/staging with full paths preserved
COPY stage-files.sh /tmp/stage-files.sh
RUN chmod +x /tmp/stage-files.sh && /tmp/stage-files.sh

# Re-declare so it is in scope for this stage (ARGs declared before the first
# FROM are not automatically visible inside stages)
ARG DISTROLESS_VARIANT=nonroot

# Grab distroless image — variant is controlled by DISTROLESS_VARIANT ARG
FROM gcr.io/distroless/cc-debian13:${DISTROLESS_VARIANT} AS common

# See https://caddyserver.com/docs/conventions#file-locations for details
ENV XDG_CONFIG_HOME=/config
ENV XDG_DATA_HOME=/data

# Required from frankenphp
ENV GODEBUG=cgocheck=0

LABEL org.opencontainers.image.title=TempestPHP
LABEL org.opencontainers.image.description="The framework that gets out of your way"
LABEL org.opencontainers.image.url=https://tempestphp.com
LABEL org.opencontainers.image.source=https://github.com/tempestphp/tempest-framework/
LABEL org.opencontainers.image.licenses=MIT
LABEL org.opencontainers.image.vendor="Brent Roose and contributors"

# All libs, binaries and config collected by stage-files.sh into /tmp/staging.
# Everything is normalised to usr/ paths so this single COPY is safe against
# the distroless /lib -> usr/lib symlink.
COPY --from=frankenphp /tmp/staging/ /

# App directories need specific ownership — distroless has no chown so we
# use the COPY --chown flag. These overwrite the copies already in /tmp/staging.
# From the gcr container, the nonroot user is uid 1002, with gid 1000
COPY --from=frankenphp --chown=1002:1000 /app /app
COPY --from=frankenphp --chown=1002:1000 /config /config
COPY --from=frankenphp --chown=1002:1000 /data /data
COPY --from=frankenphp --chown=1002:1000 /etc/frankenphp /etc/frankenphp
COPY --from=frankenphp --chown=1002:1000 /app/public/index.php /app/public/index.php

COPY Caddyfile.noworker /etc/frankenphp/Caddyfile

WORKDIR /app

EXPOSE 8000
EXPOSE 8443
EXPOSE 8443/udp
EXPOSE 2019

USER nonroot

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]
HEALTHCHECK CMD curl -f http://localhost:2019/metrics || exit 1
