<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Middleware;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Unit\Console\Fixtures\ComplexCommand;

/**
 * @internal
 * @small
 */
class InvalidCommandMiddlewareTest extends FrameworkIntegrationTestCase
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
