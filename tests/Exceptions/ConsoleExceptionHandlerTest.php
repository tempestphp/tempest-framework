<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Exceptions;

use Exception;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\Testing\TestConsoleComponentRenderer;
use Tempest\Console\Testing\TestConsoleHelper;
use Tempest\Console\Testing\TestConsoleOutput;
use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class ConsoleExceptionHandlerTest extends TestCase
{
    public function test_render_console_exception(): void
    {
        $this->container->singleton(
            ConsoleOutput::class,
            fn () => $this->container->get(TestConsoleOutput::class)
        );

        $handler = $this->container->get(ConsoleExceptionHandler::class);
        $handler->handle(new Exception('test message'));

        $output = new TestConsoleHelper(
            $this->container->get(ConsoleOutput::class),
            $this->container->get(TestConsoleComponentRenderer::class),
        );

        $output
            ->assertContains('Exception')
            ->assertContains('test message')
            ->assertContains(__FILE__)
            ->assertContains('$handler->handle(new Exception(\'test message\'));  < ');
    }
}
