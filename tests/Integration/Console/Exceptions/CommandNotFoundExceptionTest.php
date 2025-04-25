<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Exceptions;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class CommandNotFoundExceptionTest extends FrameworkIntegrationTestCase
{
    public function test_console_exception_handler(): void
    {
        $this->console
            ->call('foo:bar')
            ->assertContains('Command foo:bar not found');
    }
}
