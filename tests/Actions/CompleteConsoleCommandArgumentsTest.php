<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Actions;

use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class CompleteConsoleCommandArgumentsTest extends TestCase
{
    public function test_arguments_are_printed(): void
    {
        $this->console
            ->complete('completion:test')
            ->assertSee("--value=" . PHP_EOL)
            ->assertSee("--flag" . PHP_EOL)
            ->assertSee("--items=" . PHP_EOL);
    }

    public function test_existing_arguments_are_skipped(): void
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

    public function test_multiple_array_values_are_allowed(): void
    {
        $this->console
            ->complete('completion:test --items=a')
            ->assertSee('--items=');
    }

    public function test_open_flag_must_first_be_completed(): void
    {
        $this->console
            ->complete('completion:test --items=')
            ->assertNotSee("--value=" . PHP_EOL)
            ->assertNotSee("--flag" . PHP_EOL)
            ->assertNotSee("--items=" . PHP_EOL);
    }
}
