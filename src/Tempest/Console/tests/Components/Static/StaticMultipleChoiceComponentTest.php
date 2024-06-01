<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Components\Static;

use Tempest\Console\Console;
use Tests\Tempest\Console\ConsoleIntegrationTestCase;

/**
 * @internal
 * @small
 */
class StaticMultipleChoiceComponentTest extends ConsoleIntegrationTestCase
{
    public function test_ask(): void
    {
        $this->console
            ->call(function (Console $console) {
                $console->ask('test', ['a', 'b', 'c'], multiple: true);
            })
            ->submit('0,1')
            ->assertContains("You picked a, b;");
    }

    public function test_with_invalid_options(): void
    {
        $this->console
            ->call(function (Console $console) {
                $console->ask('test', ['a', 'b', 'c'], multiple: true);
            })
            ->submit('0,4,c,2')
            ->assertContains("You picked a, c;");
    }

    public function test_confirm(): void
    {
        $this->console
            ->call(function (Console $console) {
                $answer = $console->ask('test', ['a', 'b', 'c'], multiple: true);

                $console->writeln(json_encode($answer));
            })
            ->submit('0')
            ->assertContains("You picked a;")
            ->submit('no')
            ->assertContains('- [0] a')
            ->submit('0,1')
            ->assertContains('You picked a, b;')
            ->submit('yes')
            ->assertContains('["a","b"]');
    }
}
