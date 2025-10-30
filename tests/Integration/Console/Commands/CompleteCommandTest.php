<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CompleteCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function complete_commands(): void
    {
        $this->console
            ->complete()
            ->assertSee('migrate:up' . PHP_EOL)
            ->assertSee('schedule:run' . PHP_EOL);
    }

    #[Test]
    public function complete_arguments(): void
    {
        $this->console
            ->complete('migrate:')
            ->assertSee('migrate:down' . PHP_EOL)
            ->assertSee('migrate:up' . PHP_EOL)
            ->assertSee('migrate:rehash' . PHP_EOL);
    }
}
