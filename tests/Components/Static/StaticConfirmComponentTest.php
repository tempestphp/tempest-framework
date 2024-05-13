<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Components\Static;

use Tempest\Console\Console;
use Tempest\Console\Key;
use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class StaticConfirmComponentTest extends TestCase
{
    public function test_confirm(): void
    {
        $this->console
            ->call(function (Console $console) {
                if ($console->confirm('continue')) {
                    $console->writeln('continued');
                } else {
                    $console->writeln('not continued');
                }
            })
            ->input('yes', Key::ENTER)
            ->assertContains('continued');
    }

    public function test_not_confirm(): void
    {
        $this->console
            ->call(function (Console $console) {
                if ($console->confirm('continue')) {
                    $console->writeln('continued');
                } else {
                    $console->writeln('not continued');
                }
            })
            ->input('no', Key::ENTER)
            ->assertContains('continued');
    }

    public function test_with_default(): void
    {
        $this->console
            ->call(function (Console $console) {
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
            ->call(function (Console $console) {
                if ($console->confirm('continue')) {
                    $console->writeln('continued');
                } else {
                    $console->writeln('not continued');
                }
            })
            ->input(Key::ENTER)
            ->assertContains('not continued');
    }
}
