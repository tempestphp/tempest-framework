<?php

namespace Tests\Tempest\Console\Exceptions;

use Exception;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\Testing\Console\TestConsoleHelper;
use Tests\Tempest\Console\TestCase;

class ConsoleExceptionHandlerTest extends TestCase
{
    public function test_render_console_exception(): void
    {
        $handler = $this->container->get(ConsoleExceptionHandler::class);
        $handler->handle(new Exception('test message'));

        $output = new TestConsoleHelper(
            $this->container->get(ConsoleOutput::class),
        );

        $output
            ->assertContains('Exception')
            ->assertContains('test message')
            ->assertContains(__FILE__)
            ->assertContains('$handler->handle(new Exception(\'test message\'));  < ');
    }
}
