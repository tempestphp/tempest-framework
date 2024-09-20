<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Components\Static;

use Tempest\Console\Console;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

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
            ->assertContains("Hello Brent");
    }
}
