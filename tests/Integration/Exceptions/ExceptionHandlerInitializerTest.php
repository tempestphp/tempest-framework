<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Exceptions;

use Tempest\Exceptions\ConsoleExceptionHandler;
use Tempest\Exceptions\ExceptionHandlerInitializer;
use Tempest\Exceptions\HttpExceptionHandler;
use Tests\Tempest\Integration\TestCase;

class ExceptionHandlerInitializerTest extends TestCase
{
    /** @test */
    public function exception_handler_for_http()
    {
        $this->actAsHttpApplication();

        $exceptionHandler = (new ExceptionHandlerInitializer())->initialize($this->container);

        $this->assertInstanceOf(HttpExceptionHandler::class, $exceptionHandler);
    }

    /** @test */
    public function exception_handler_for_console()
    {
        $this->actAsConsoleApplication();

        $exceptionHandler = (new ExceptionHandlerInitializer())->initialize($this->container);

        $this->assertInstanceOf(ConsoleExceptionHandler::class, $exceptionHandler);
    }
}
