<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Testing;

use Tempest\Console\Console;
use Tests\Tempest\Console\Fixtures\ComplexCommand;
use Tests\Tempest\Console\Fixtures\InteractiveCommand;
use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class ConsoleTesterTest extends TestCase
{
    public function test_call_with_invokable(): void
    {
        $this->console
            ->call(ComplexCommand::class)
            ->assertContains('Provide missing input');
    }

    public function test_call_with_closure(): void
    {
        $this->console
            ->call(function (Console $console) {
                $console->writeln('hi');
            })
            ->assertContains('hi');
    }

    public function test_call_with_callable(): void
    {
        $this->console
            ->call([InteractiveCommand::class, 'validation'])
            ->assertContains('a');
    }

    public function test_call_with_command(): void
    {
        $this->console
            ->call('interactive:validation')
            ->assertContains('a');
    }
}
