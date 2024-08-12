<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components\Static;

use Tempest\Console\Console;
use Tempest\Console\Key;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class StaticSingleChoiceComponentTest extends FrameworkIntegrationTestCase
{
    public function test_with_options(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $answer = $console->ask('test', ['a', 'b']);

                $console->writeln("picked {$answer}");
            })
            ->input(Key::ENTER)
            ->assertDoesNotContain('picked a')
            ->submit('a')
            ->assertContains('picked a');
    }

    public function test_with_default_option(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $answer = $console->ask('test', ['a', 'b'], default: 'b');

                $console->writeln("picked {$answer}");
            })
            ->input(Key::ENTER)
            ->assertContains('picked b');
    }

    public function test_as_list(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $answer = $console->ask('test', ['a', 'b'], asList: true);

                $console->writeln("picked {$answer}");
            })
            ->submit(1)
            ->assertContains('picked b');
    }

    public function test_as_list_with_default(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $answer = $console->ask('test', ['a', 'b'], default: 1, asList: true);

                $console->writeln("picked {$answer}");
            })
            ->input(Key::ENTER)
            ->assertContains('picked b');
    }
}
