<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Exceptions;

use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class InvalidCommandExceptionTest extends TestCase
{
    public function test_console_exception_handler(): void
    {
        $this->console
            ->call('complex')
            ->assertContains('Invalid command usage:')
            ->assertContains('complex <a> <b> <c>')
            ->assertContains('Missing arguments: a, b, c');

        $this->console
            ->call('complex a')
            ->assertContains('Missing arguments: b, c');

        $this->console
            ->call('complex a b c')
            ->assertDoesNotContain('Invalid command usage')
            ->assertDoesNotContain('Missing');
    }
}
