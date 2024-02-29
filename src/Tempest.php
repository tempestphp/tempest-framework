<?php

declare(strict_types=1);

namespace Tempest;

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
    ) {
    }

    public static function boot(string $root): self
    {
        $dotenv = Dotenv::createUnsafeImmutable($root);
        $dotenv->safeLoad();

        $createAppConfig = static fn (): AppConfig => new AppConfig(
            environment: Environment::from(env('ENVIRONMENT', Environment::LOCAL->value)),
            discoveryCache: env('DISCOVERY_CACHE', false),
            enableExceptionHandling: env('EXCEPTION_HANDLING', false),
        );

        $kernel = new Kernel(
            root: $root,
            appConfig: $createAppConfig(),
        );

        return new self(
            kernel: $kernel,
        );
    }

    public function console(): ConsoleApplication
    {
        $container = $this->kernel->init();
        $appConfig = $container->get(AppConfig::class);

        $application = new ConsoleApplication(
            args: $_SERVER['argv'],
            container: $container,
            appConfig: $appConfig,
        );

        $container->singleton(Application::class, fn () => $application);

        $appConfig->exceptionHandlers[] = $container->get(ExceptionHandler::class);

        return $application;
    }

    public function http(): HttpApplication
    {
        $container = $this->kernel->init();
        $appConfig = $container->get(AppConfig::class);

        $application = new HttpApplication(
            container: $container,
            appConfig: $appConfig,
        );

        $container->singleton(Application::class, fn () => $application);

        $appConfig->exceptionHandlers[] = $container->get(ExceptionHandler::class);

        return $application;
    }

    public function kernel(): Kernel
    {
        return $this->kernel;
    }
}
