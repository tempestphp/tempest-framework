<?php

declare(strict_types=1);

namespace Tempest;

use Closure;
use Dotenv\Dotenv;
use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\Environment;
use Tempest\Application\HttpApplication;
use Tempest\Application\Kernel;
use Tempest\Exceptions\ExceptionHandler;

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
        $container = $this->kernel->init();

        $application = new ConsoleApplication(
            args: $_SERVER['argv'],
            container: $container,
            appConfig: $this->appConfig,
        );

        $container->singleton(Application::class, fn () => $application);

        $this->appConfig->exceptionHandlers[] = $container->get(ExceptionHandler::class);

        return $application;
    }

    public function http(): HttpApplication
    {
        $container = $this->kernel->init();

        $application = new HttpApplication(
            container: $container,
            appConfig: $this->appConfig,
        );

        $container->singleton(Application::class, fn () => $application);
        
        $this->appConfig->exceptionHandlers[] = $container->get(ExceptionHandler::class);

        return $application;
    }

    public function kernel(): Kernel
    {
        return $this->kernel;
    }
}
