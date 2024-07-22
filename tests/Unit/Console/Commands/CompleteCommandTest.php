<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Commands;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class CompleteCommandTest extends FrameworkIntegrationTestCase
{
    public function test_complete_commands(): void
    {
        $this->console
            ->complete()
            ->assertSee('tail:server' . PHP_EOL)
            ->assertSee('schedule:run' . PHP_EOL);
    }

    public function test_complete_arguments(): void
    {
        $this->console
            ->complete('tail:')
            ->assertSee('tail:server' . PHP_EOL)
            ->assertSee('tail:project' . PHP_EOL)
            ->assertSee('tail:debug' . PHP_EOL);
    }
}
