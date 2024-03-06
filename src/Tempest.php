<?php

declare(strict_types=1);

namespace Tempest;

use Dotenv\Dotenv;
use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\Environment;
use Tempest\Application\HttpApplication;
use Tempest\Application\Kernel;
use Tempest\Exceptions\ConsoleExceptionHandler;
use Tempest\Exceptions\HttpExceptionHandler;

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

        $kernel = new Kernel(
            root: $root,
            appConfig: new AppConfig(
                root: $root,
                environment: Environment::from(env('ENVIRONMENT', Environment::LOCAL->value)),
                discoveryCache: env('DISCOVERY_CACHE', false),
                enableExceptionHandling: env('EXCEPTION_HANDLING', false),
            ),
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

        $appConfig->exceptionHandlers[] = $container->get(ConsoleExceptionHandler::class);

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

        $appConfig->exceptionHandlers[] = $container->get(HttpExceptionHandler::class);

        return $application;
    }

    public function kernel(): Kernel
    {
        return $this->kernel;
    }
}
