<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Middleware;

use Tests\Tempest\Console\ConsoleIntegrationTestCase;
use Tests\Tempest\Console\Fixtures\ComplexCommand;

/**
 * @internal
 * @small
 */
class InvalidCommandMiddlewareTest extends ConsoleIntegrationTestCase
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
