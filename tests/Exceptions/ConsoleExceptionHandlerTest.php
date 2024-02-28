<?php

declare(strict_types=1);

namespace Tests\Tempest\Exceptions;

use App\Console\FailCommand;
use Tempest\Exceptions\ConsoleExceptionHandler;
use Tests\Tempest\TestCase;

class ConsoleExceptionHandlerTest extends TestCase
{
    /** @test */
    public function test_exception()
    {
        $this->appConfig->enableExceptionHandling = true;
        $this->appConfig->exceptionHandlers = [
            $this->container->get(ConsoleExceptionHandler::class),
        ];

        $output = $this->console('fail output')->asText();

        $this->assertStringContainsString(FailCommand::class, $output);
        $this->assertStringContainsString('__invoke', $output);
        //        $this->assertStringContainsString("'output'", $output);
    }
}
