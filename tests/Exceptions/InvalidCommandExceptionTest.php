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
            ->assertContains('Invalid command')
            ->assertContains('Argument a is missing')
            ->assertContains('Argument b is missing')
            ->assertContains('Argument c is missing');

        $this->console
            ->call('complex a')
            ->assertDoesNotContain('Argument a is missing')
            ->assertDoesNotContain('Argument #0 is missing')
            ->assertContains('Invalid command')
            ->assertContains('Argument b is missing')
            ->assertContains('Argument c is missing');

        $this->console
            ->call('complex a b c')
            ->assertDoesNotContain('Invalid command')
            ->assertDoesNotContain('Argument a is missing')
            ->assertDoesNotContain('Argument b is missing')
            ->assertDoesNotContain('Argument c is missing');
    }
}
