<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Exceptions;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CommandNotFoundExceptionTest extends FrameworkIntegrationTestCase
{
    public function test_console_exception_handler(): void
    {
        $this->console
            ->call('foo:bar')
            ->assertContains('Command foo:bar not found');
    }
}
