<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Middleware;

use Tests\Tempest\Integration\Console\Fixtures\ComplexCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class InvalidCommandMiddlewareTest extends FrameworkIntegrationTestCase
{
    public function test_provide_missing_input(): void
    {
        $this->console
            ->call(ComplexCommand::class)
            ->assertContains('Provide missing input')
            ->submit('a')
            ->submit('b')
            ->submit('c')
            ->assertContains('abc');
    }
}
