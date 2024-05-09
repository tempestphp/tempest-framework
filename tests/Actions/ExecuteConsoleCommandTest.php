<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Actions;

use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class ExecuteConsoleCommandTest extends TestCase
{
    public function test_command_specific_middleware(): void
    {
        $this->console
            ->call('with:middleware')
            ->assertContains('from middleware')
            ->assertContains('from command');
    }
}
