<?php

declare(strict_types=1);

namespace Tests\Tempest\Exceptions;

use Tempest\AppConfig;
use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\HttpApplication;
use Tempest\Exceptions\ConsoleExceptionHandler;
use Tempest\Exceptions\ExceptionHandlerInitializer;
use Tempest\Exceptions\HttpExceptionHandler;
use Tests\Tempest\TestCase;

class ExceptionHandlerInitializerTest extends TestCase
{
    /** @test */
    public function exception_handler_for_http()
    {
        $this->container->singleton(Application::class, fn () => new HttpApplication(
            $this->container,
            $this->container->get(AppConfig::class),
        ));

        $exceptionHandler = (new ExceptionHandlerInitializer())->initialize($this->container);

        $this->assertInstanceOf(HttpExceptionHandler::class, $exceptionHandler);
    }

    /** @test */
    public function exception_handler_for_console()
    {
        $this->container->singleton(Application::class, fn () => new ConsoleApplication(
            [],
            $this->container,
            $this->container->get(AppConfig::class),
        ));

        $exceptionHandler = (new ExceptionHandlerInitializer())->initialize($this->container);

        $this->assertInstanceOf(ConsoleExceptionHandler::class, $exceptionHandler);
    }
}
