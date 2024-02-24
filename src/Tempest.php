<?php

declare(strict_types=1);

namespace Tempest;

use Closure;
use Dotenv\Dotenv;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\Environment;
use Tempest\Application\HttpApplication;
use Tempest\Application\Kernel;

final readonly class Tempest
{
    private function __construct(
        private Kernel $kernel,
        private AppConfig $appConfig,
    ) {
    }

    public static function boot(string $root, ?Closure $createAppConfig = null): self
    {
        $dotenv = Dotenv::createUnsafeImmutable($root);
        $dotenv->safeLoad();

        $createAppConfig ??= fn () => new AppConfig(
            environment: Environment::from(env('ENVIRONMENT', Environment::LOCAL->value)),
            discoveryCache: env('DISCOVERY_CACHE', false),
        );

        $appConfig = $createAppConfig();

        $kernel = new Kernel($root, $appConfig);

        return new self(
            kernel: $kernel,
            appConfig: $appConfig
        );
    }

    public function console(): ConsoleApplication
    {
        return new ConsoleApplication(
            args: $_SERVER['argv'],
            container: $this->kernel->init(),
        );
    }

    public function http(): HttpApplication
    {
        return new HttpApplication(
            container: $this->kernel->init(),
        );
    }

    public function kernel(): Kernel
    {
        return $this->kernel;
    }
}
