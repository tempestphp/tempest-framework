---
title: Docker
description: Tempest can both be developed or deployed in Production, with our own Docker images. Or, copy the Dockerfiles and customise as you need with our utility commands.
---

## Overview

We are pleased to offer our own set of Docker images for developing with and serving your Tempest applications, for you to use as and customise as you see fit.

In order to start from the strongest security posture and enable you to run secure and performant Tempest-based applications, we've initially selected FrankenPHP as our server of choice. Further, we've adopted a 'rootless' approach by default, and also offer a 'distroless' production image to further mitigate potential security issues stemming from unnecessary software often found in Docker images.

## Image architecture, variants and release strategy

Our CI/CD will automatically generate and publish images to our public repository at https://PLACE.HOLD.ER/tempestphp/ship following the releases of PHP and FrankenPHP, and also any time we find an issue in the underlying Docker image. Alternatively, you can also customise these images for your own use, see section below. (TODO: link)

### Architectures

We publish `amd64` AKA `x86_64`, and `aarch64` AKA `arm64` releases, which should work on most Linux and MacOS host systems, as part of a multi-arch image. The appropriate version should be selected automatically for your host system by docker when providing the image.

Our upstream providers have some support for other architectures, should you need to support other platforms; see Customising the Docker image for your use, below.

### Variants and release strategy

We maintain two variants; 'latest' which is rootless and distroless, and is aimed at your test, qa and production needs, and 'debug' which is the same base image, with busybox available in case you need to access the docker shell.

```bash
# These periodically updated variant tags will always point at the latest version-pinned images
tempestphp/ship                  >>            tempestphp/ship:1.11.3-8.5.3         #at time of writing
tempestphp/ship:latest           >>            tempestphp/ship:1.11.3-8.5.3         #at time of writing
tempestphp/ship:debug            >>            tempestphp/ship:1.11.3-8.5.3-debug   #at time of writing

# We'll also continually publish pinned-versions
tempestphp/ship:1.11.3-8.5.3
tempestphp/ship:1.11.3-8.5.3-debug
# these will accumulate over time
```
We utilise the [GoogleContainerTools Distroless](https://github.com/GoogleContainerTools/distroless/) [`cc`](https://github.com/GoogleContainerTools/distroless/blob/main/cc/README.md) image, pulling their latest 'nonroot' image as our base, at time of build.
```dockerfile
FROM gcr.io/distroless/cc-debian13:nonroot AS runner
```

### Response to security incidents in the software chain

We monitor our three upstream providers, GoogleContainerTools, PHP, and FrankenPHP, for security defect announcements.

- We will actively replace pinned-version images utilising an instance of the distroless base image found to have any security defects.
- We will actively retire pinned-version images utilising an instance of PHP or FrankenPHP releases found to have any security defects.

:::info
We won't automatically retire 'patch' version releases i.e. PHP8.5.3 > PHP8.5.4, unless subject to security defects specifically, in which case we'll re-build the image and re-publish. We encourage you to monitor the upstreams and update regularly, or use the `:debug` or `:latest` releases where possible.
:::

## Developing your application with Docker

During development, we'd suggest using the debug image. We've included a convenience command in the `tempest/ship` package which will run a development server on your device.
```bash
./tempest ship:serve                       # by default, this will get debug from the repository and serve it
```
You may specify the `latest` image if you prefer.
```bash
./tempest ship:serve latest                # latest floating version
```
You may instead specify the release, if you require a pinned-version.
```bash
./tempest ship:serve 1.11.3-8.5.3          # pinned-version, distroless
./tempest ship:serve 1.11.3-8.5.3-debug    # pinned-version, debug
```

:::info
By default, the `ship:serve` command will try to pull from the registry. But if you have published the stub for customising the image, this command will attempt to use the local image. You can force this behaviour by adding the optional command `--repository=local` or `--repository=remote`.
:::

## Testing and production applications with Docker

For testing and QA, we'd suggest using the distroless image, as it is most representative of your final infrastructure, and should highlight any issues for your attention.
```bash
./tempest ship:serve latest
```
As per the section above, you can omit `latest` to default to the `debug` release, or specify a version.

## Customising the Docker image for your use

As the `latest` and `debug` images are inherently distroless, albeit with busybox in the `debug` image, you cannot use this as an intermediate stage in a multi-stage Dockerfile build. Instead, you can use the `ship:publish` Tempest command to publish a copy of the stubs, so you can build and tweak as you need.
```bash
./tempest ship:publish                     # by default, this will publish the debug dockerfile
./tempest ship:publish:latest              # select the distroless image, instead
```
This will publish `.dockerignore`, `Caddyfile`, and `Dockerfile` into your project root `docker/` folder, creating it as necessary. If you already have files in here, it shouldn't overwrite by default.

You can also retrieve the files manually, from the vendor folder.
```bash
vendor/tempest/framework/packages/ship/stubs/
```
### Building the image

We've provided a simple `ship:build` command to build these local images. It won't handle all use cases, and is really only aimed at someone directly running the images. If you are ready to change the Dockerfile to suit your needs, you probably won't want to use this anyway. That said, here's how to use it.

If you HAVE NOT published the stubs to your project:
```bash
./tempest ship:build               # will attempt to build debug directly from the package stubs folder
./tempest ship:build debug         # will attempt to build debug directly from the package stubs folder
./tempest ship:build latest        # will attempt to build distroless directly from the package stubs folder
```
If you HAVE published the stubs to your project:
```bash
./tempest ship:build               # will attempt to build debug from `{root_path}/docker/`
./tempest ship:build debug         # will attempt to build debug from `{root_path}/docker/`
./tempest ship:build latest        # will attempt to build distroless from `{root_path}/docker/`
```
:::info
If you've published both stubs, or renamed the Dockerfile, this won't work. You've moved past the use-case this command was designed for, and will need to build yourself. Or copy the ShipBuildCommand into your project and customise it to suit you!
:::

### Default versions of FrankenPHP and PHP

We will update the stubs from time-to-time, but you may find that your PHP and/or FrankenPHP versions are out of step, because you have customised your file and don't wish to republish the stubs losing the changes.

You can use the `ship:build` command to pass the arguments:
```bash
./tempest ship:build {''|debug|latest} --with-frankenphp="1.11.3" --with-php="8.5.3"
```
:::info
Note that this will tag the image with 'tempestphp/ship:debug' or 'tempestphp/ship:latest', and remains compatible with `ship:serve`.
:::

Or, you pass these via build arguments run from the `{root_path}/docker/` folder:
```bash
docker build . -t tempestphp/ship:1.11.3-8.5.3 --build-arg FRANKENPHP_VERSION="1.11.3" --build-arg PHP_VERSION="8.5.3"
```
:::info
To retain compatibility with `ship:serve` ensure that the image retains 'tempestphp/ship:' in the filename and then pass `1.11.3-8.5.3` as the image variant i.e. `./tempest ship:serve 1.11.3-8.5.3`.
:::

Or you can edit the Dockerfile directly:
```bash
ARG FRANKENPHP_VERSION=1.11.3
ARG PHP_VERSION=8.5.3
```
:::info
This method also retains compatibility with `ship:serve` and `ship:build`, as long as you keep the filename unchanged.
:::

## Adding additional PHP Extensions

We include PHP Extensions from [Marc Henderkes'](https://pkgs.henderkes.com/) Static PHP Repository. These are static builds, of PHP-ZTS, which is required by FrankenPHP.

:::info
Note that apt-get packages are kebab-case and should be prefixed `php-zts`. So if you wanted the extension `pdo_mysql`, you'd specify `php-zts-pdo-mysql`. The command won't convert the syntax automatically.
:::

### Adding extensions at build time via build arguments

This method is useful if you need to make a specific build one-off, containing an additional extension.

Pass the build argument directly if using a published stub Dockerfile:
```bash
docker build . -t ship:with-yaml --build-arg PHP_EXTRA_EXTENSIONS="php-zts-yaml"
```
Or, you can use the Tempest `ship:build` command and pass the optional argument:
```bash
./tempest ship:build --with-php-extensions="php-zts-yaml"
```
:::info
This will work with both the `debug` and `latest` images.
:::

### Adding extensions to the Dockerfile

This method is useful if you want to make your own image which always includes

```dockerfile
RUN 
    # cropped for brevity
    apt-get install -y --download-only --no-install-recommends \
        ca-certificates \
        frankenphp \
        php-zts-gd \
        php-zts-intl \
        php-zts-mysqli \
        php-zts-pdo-mysql \
        php-zts-pdo-pgsql \
        php-zts-pdo-sqlite \
        php-zts-redis \
        # Insert additional extensions here, space separated, or one per line followed by 'space, slash' i.e. ' \'
        php-zts-zip ${PHP_EXTRA_EXTENSIONS}; \
```

## Composer

We don't package composer in the image currently, mostly due to the lack of shell in the distroless image. While it could potentially be included in the debug image, the primary use for the debug image is likely to be a developer's local machine, with a volume mount for the app. The host itself will almost certainly have composer installed as part of the developer's IDE and toolset, meaning it's presence in debug is largely redundant and unlikely to be used commonly. It would also have unequal updates since we are not version-pinning composer, resulting in stale versions present in the docker volumes.

We suggest one of the following options instead.

### Run composer from docker composer:latest

You can use the following command to run composer interactively:
```bash
docker run --rm -i --tty --volume $PWD:/app --user 1002:1002 composer:latest # We suggest running with 1002:1002 to match the file permissions within our rootless images
```
You could also create an alias script:
```bash
sudo sh -c 'echo "#!/usr/bin/env sh\ndocker run --rm -it --volume \"\$PWD:/app\" --user 1002:1002 composer:latest composer \"\$@\"" > /usr/local/bin/composer' && sudo chmod +x /usr/local/bin/composer
```
This would allow you to execute `composer install` from the command line, via docker, without it being installed on your host.

:::info
You can find more detailed instructions for running composer via docker [here](https://github.com/docker-library/docs/tree/master/composer).
:::

## Environment variables

The following .env variables are exposed within the Caddyfile. Those requiring defaults already have them, so in many cases you can simply ignore these.

```bash
# Defaults to 8000, can be changed to any port above 1024
CADDY_HTTP_PORT
# Defaults to 8443, can be changed to any port above 1024, must be unique
CADDY_HTTPS_PORT
# Use this to insert any Caddy global options, carried forward from FrankenPHP
CADDY_GLOBAL_OPTIONS
# Use this to specify any FrankenPHP global options, carried forward from FrankenPHP
FRANKENPHP_CONFIG

# Use this to specify any extra Caddy config that doesn't belong in the global or site blocks, carried forward from FrankenPHP
CADDY_EXTRA_CONFIG

# Specify the FQDN, defaults to localhost
CADDY_SERVER_NAME
# Where to serve the app from, defaults to public/, meaning /app/public/ as app is the WORKDIR
CADDY_SERVER_ROOT

# Configure the mercure module, carried forward from FrankenPHP
MERCURE_PUBLISHER_JWT_KEY
MERCURE_PUBLISHER_JWT_ALG
MERCURE_SUBSCRIBER_JWT_KEY
MERCURE_SUBSCRIBER_JWT_ALG
MERCURE_EXTRA_DIRECTIVES

# Any additional Caddy directives for the site-block, carried forward from FrankenPHP
CADDY_SERVER_EXTRA_DIRECTIVES
```

You can also map a volume to a folder containing Caddyfile and pass your own Caddyfile, should you wish. Assuming that you've stored the Caddyfile in `/my/local/caddyconfig`:
```bash
docker run -v /my/local/caddyconfig:/etc/frankenphp/
```

## Frequently asked questions

### Why no Alpine image?

FrankenPHP strongly recommends not using Alpine for production environments - see [Don't Use Musl on FrankenPHP docs](https://frankenphp.dev/docs/performance/#dont-use-musl) - due to performance loss under ZTS mode. We decided not to offer even a development image on Alpine, since your application should be developed on a comparable environment to ensure consistency throughout.

One of the main benefits of Alpine is that it's typically considered 'distroless' being built up from an empty system, and smaller. We chose to address this by selecting a distroless Debian Trixie base image, which while not as small as Alpine, is still minimised and at time of writing approximately 263MB.

:::info
For comparison, the FrankenPHP official images at time of writing come in at 182MB for Alpine and 613MB for Debian, neither of which include all the extensions we add. This means our 'distroless' image is actually very similar in size to the Alpine image.
:::

### Why no install-php-extensions, PHPIZE, PIE etc?

Put simply, the utilities (apt, deb, make, build-essentials, etc etc) required to install with these tools add a lot of bloat to the final image. They're only needed at build time, and potentially represent a security risk should we leave them within the final image. So, we recommend you do not install such utilities and instead use the provided mechanisms above to install from the provided repository.

### Why aren't you using Sury's / other repository?

Sury's PHP repository doesn't offer PHP-ZTS, and the Henderkes repository has the benefit of being officially-recognised by the FrankenPHP team; it is the repo in their documentation for apt-get / rpm / apk installs for FrankenPHP itself, and as a consequence also has the PHP-ZTS packages to link with it.

The Henderkes repos are also static, which is significantly cleaner for a distroless image, as the only dependency they have is effectively just `gcc-base`.

## More questions?

- [Join the Discord server](https://tempestphp.com/discord)
- [Raise an issue on github](https://github.com/tempestphp/tempest-framework/issues)