<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Exceptions;

use App\Console\FailCommand;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class ConsoleExceptionHandlerTest extends FrameworkIntegrationTestCase
{
    public function test_exception()
    {
        $this->appConfig->enableExceptionHandling = true;
        $this->appConfig->exceptionHandlers = [
            $this->container->get(ConsoleExceptionHandler::class),
        ];

        $this->console
            ->call('fail output')
            ->assertContains(FailCommand::class)
            ->assertContains('__invoke');
    }
}
