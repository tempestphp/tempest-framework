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
        // Fix for classes that don't have a proper PSR-4 namespace,
        // they break discovery with an unrecoverable error,
        // but you don't know why because PHP simply says "duplicate classname" instead of something reasonable.
        register_shutdown_function(function (): void {
            $error = error_get_last();

            $message = $error['message'] ?? '';

            if (str_contains($message, 'Cannot declare class')) {
                echo "Does this class have the right namespace?" . PHP_EOL;
            }
        });

        $root ??= getcwd();

        // Env
        $dotenv = Dotenv::createUnsafeImmutable($root);
        $dotenv->safeLoad();

        // AppConfig
        $appConfig ??= new AppConfig(
            root: $root,
            environment: Environment::from(env('ENVIRONMENT', Environment::LOCAL->value)),
            discoveryCache: env('DISCOVERY_CACHE', false),
        );

        $appConfig->exceptionHandlerSetup->setup($appConfig);

        // Kernel
        return (new Kernel(
            appConfig: $appConfig,
        ))->init();
    }
}
