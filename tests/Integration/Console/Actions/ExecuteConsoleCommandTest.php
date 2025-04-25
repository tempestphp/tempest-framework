<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Actions;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Console\GenericConsole;
use Tests\Tempest\Integration\Console\Fixtures\ArrayInputCommand;
use Tests\Tempest\Integration\Console\Fixtures\CommandWithMiddleware;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class ExecuteConsoleCommandTest extends FrameworkIntegrationTestCase
{
    public function test_command_specific_middleware(): void
    {
        $this->console
            ->call('with:middleware')
            ->assertContains('from middleware')
            ->assertContains('from command');
    }

    public function test_command_specific_middleware_through_console(): void
    {
        $this->console
            ->call(fn (GenericConsole $console) => $console->call('with:middleware'))
            ->assertContains('from middleware')
            ->assertContains('from command');
    }

    public function test_call_command_by_class_name(): void
    {
        $this->console
            ->call(CommandWithMiddleware::class)
            ->assertContains('from middleware')
            ->assertContains('from command');
    }

    public function test_call_command_by_class_name_with_parameters(): void
    {
        $this->console
            ->call(ArrayInputCommand::class, ['--input=a', '--input=b'])
            ->assertSee('["a","b"]');
    }

    public function test_command_with_positional_argument_with_space(): void
    {
        $this->markTestSkipped('Failing test.');

        // $this->console
        //     ->call('complex a "b b" c --flag')
        //     ->assertSee('ab bc');
    }
}
