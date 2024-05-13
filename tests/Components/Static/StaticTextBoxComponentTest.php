<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Components\Static;

use Tempest\Console\Console;
use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class StaticTextBoxComponentTest extends TestCase
{
    public function test_text_box(): void
    {
        $this->console
            ->call(function (Console $console) {
                $name = $console->ask('test');

                $console->writeln("Hello {$name}");
            })
            ->submit('Brent')
            ->assertContains("Hello Brent");
    }
}
