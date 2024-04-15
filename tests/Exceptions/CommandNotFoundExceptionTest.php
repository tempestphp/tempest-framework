<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Exceptions;

use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class CommandNotFoundExceptionTest extends TestCase
{
    public function test_console_exception_handler(): void
    {
        $this->console
            ->call('foo:bar')
            ->assertContains('Command foo:bar not found');
    }
}
