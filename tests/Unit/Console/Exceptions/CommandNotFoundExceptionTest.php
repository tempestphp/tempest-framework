<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Exceptions;

use Tests\Tempest\Unit\Console\ConsoleIntegrationTestCase;

/**
 * @internal
 * @small
 */
class CommandNotFoundExceptionTest extends ConsoleIntegrationTestCase
{
    public function test_console_exception_handler(): void
    {
        $this->console
            ->call('foo:bar')
            ->assertContains('Command foo:bar not found');
    }
}
