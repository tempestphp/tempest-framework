<?php

namespace Tests\Tempest\Console\Exceptions;

use Tempest\Console\ConsoleStyle;
use Tests\Tempest\Console\TestCase;

class CommandNotFoundExceptionTest extends TestCase
{
    public function test_console_exception_handler(): void
    {
        $this->console
            ->call('foo:bar')
            ->assertContains('Command foo:bar not found')
            ->assertContainsFormattedText(ConsoleStyle::FG_DARK_RED(ConsoleStyle::UNDERLINE('foo:bar')))
        ;
    }
}
