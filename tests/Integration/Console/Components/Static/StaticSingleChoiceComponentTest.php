<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components\Static;

use Tempest\Console\Console;
use Tempest\Console\Key;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class StaticSingleChoiceComponentTest extends FrameworkIntegrationTestCase
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
                $answer = $console->ask('test', ['a', 'b'], multiple: true);

                $console->writeln("picked {$answer}");
            })
            ->submit(1)
            ->assertContains('picked b');
    }

    public function test_as_list_with_default(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $answer = json_encode($console->ask('test', ['a', 'b'], default: 'a', multiple: true));

                $console->writeln("picked {$answer}");
            })
            ->input(Key::ENTER)
            ->input('yes')
            ->assertContains('picked ["a"]');
    }

    public function test_with_default_option_without_prompting(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                $answer = $console->ask('test', ['a', 'b'], default: 'b');

                $console->writeln("picked {$answer}");
            })
            ->assertContains('picked b');
    }
}
