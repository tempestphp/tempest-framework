<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class CompleteCommandTest extends FrameworkIntegrationTestCase
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
