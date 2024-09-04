<?php

declare(strict_types=1);

namespace Tempest\Core;

use Dotenv\Dotenv;
use Tempest\Container\Container;
use function Tempest\env;

final readonly class Tempest
{
    public static function boot(?string $root = null, ?AppConfig $appConfig = null): Container
    {
        $root ??= getcwd();

        // Env
        $dotenv = Dotenv::createUnsafeImmutable($root);
        $dotenv->safeLoad();

        // AppConfig
        $appConfig ??= new AppConfig(
            root: $root,
            environment: Environment::from(env('ENVIRONMENT', Environment::LOCAL->value)),
            enableExceptionHandling: env('EXCEPTION_HANDLING', false),
            discoveryCache: env('DISCOVERY_CACHE', false),
        );

        // Kernel
        return (new Kernel(
            appConfig: $appConfig,
        ))->init();
    }
}
