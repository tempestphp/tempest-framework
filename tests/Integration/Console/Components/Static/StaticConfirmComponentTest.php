<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components\Static;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\Console;
use Tempest\Console\Key;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class StaticConfirmComponentTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function confirm(): void
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

    #[Test]
    public function not_confirm(): void
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

    #[Test]
    public function with_default(): void
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

    #[Test]
    public function without_default(): void
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

    #[Test]
    public function with_default_without_prompting(): void
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
