<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components\Static;

use Tempest\Console\Console;
use Tempest\Drift\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class StaticTextBoxComponentTest extends FrameworkIntegrationTestCase
{
    public function test_text_box(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $name = $console->ask('test');

                $console->writeln("Hello {$name}");
            })
            ->submit('Brent')
            ->assertContains('Hello Brent');
    }

    public function test_supports_default(): void
    {
        $this->console
            ->call(function (Console $console): void {
                $name = $console->ask('test', default: 'Brent');

                $console->writeln("Hello {$name}");
            })
            ->submit()
            ->assertContains('Hello Brent');
    }

    public function test_supports_default_without_prompting(): void
    {
        $this->console
            ->withoutPrompting()
            ->call(function (Console $console): void {
                $name = $console->ask('test', default: 'Brent');

                $console->writeln("Hello {$name}");
            })
            ->assertContains('Hello Brent');
    }
}
