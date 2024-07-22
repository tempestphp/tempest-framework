<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Actions;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class ExecuteConsoleCommandTest extends FrameworkIntegrationTestCase
{
    public function test_command_specific_middleware(): void
    {
        $this->console
            ->call('with:middleware')
            ->assertContains('from middleware')
            ->assertContains('from command');
    }
}
