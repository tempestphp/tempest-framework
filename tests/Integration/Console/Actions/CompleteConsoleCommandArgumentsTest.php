<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Actions;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CompleteConsoleCommandArgumentsTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function arguments_are_printed(): void
    {
        $this->console
            ->complete('completion:test')
            ->assertSee('--value=' . PHP_EOL)
            ->assertSee('--flag' . PHP_EOL)
            ->assertSee('--items=' . PHP_EOL);
    }

    #[Test]
    public function existing_arguments_are_skipped(): void
    {
        $this->console
            ->complete('completion:test --flag')
            ->assertNotSee('--flag');

        $this->console
            ->complete('completion:test --flag=false')
            ->assertNotSee('--flag');

        $this->console
            ->complete('completion:test --value=bar')
            ->assertNotSee('--value');
    }

    #[Test]
    public function multiple_array_values_are_allowed(): void
    {
        $this->console
            ->complete('completion:test --items=a')
            ->assertSee('--items=');
    }

    #[Test]
    public function open_flag_must_first_be_completed(): void
    {
        $this->console
            ->complete('completion:test --items=')
            ->assertNotSee('--value=' . PHP_EOL)
            ->assertNotSee('--flag' . PHP_EOL)
            ->assertNotSee('--items=' . PHP_EOL);
    }
}
