<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components\Static;

use Tempest\Console\Console;
use Tempest\Console\Key;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class StaticConfirmComponentTest extends FrameworkIntegrationTestCase
{
    public function test_confirm(): void
    {
        $this->console
            ->call(function (Console $console): void {
                if ($console->confirm('continue')) {
                    $console->writeln('continued');
                } else {
                    $console->writeln('not continued');
                }
            })
            ->submit('yes')
            ->assertContains('continued');
    }

    public function test_not_confirm(): void
    {
        $this->console
            ->call(function (Console $console): void {
                if ($console->confirm('continue')) {
                    $console->writeln('continued');
                } else {
                    $console->writeln('not continued');
                }
            })
            ->submit('no')
            ->assertContains('continued');
    }

    public function test_with_default(): void
    {
        $this->console
            ->call(function (Console $console): void {
                if ($console->confirm('continue', default: true)) {
                    $console->writeln('continued');
                } else {
                    $console->writeln('not continued');
                }
            })
            ->input(Key::ENTER)
            ->assertContains('continued');
    }

    public function test_without_default(): void
    {
        $this->console
            ->call(function (Console $console): void {
                if ($console->confirm('continue')) {
                    $console->writeln('continued');
                } else {
                    $console->writeln('not continued');
                }
            })
            ->input(Key::ENTER)
            ->assertContains('not continued');
    }

    public function test_with_default_without_prompting(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                if ($console->confirm('continue', default: true)) {
                    $console->writeln('continued');
                } else {
                    $console->writeln('not continued');
                }
            })
            ->assertContains('continued');
    }
}
