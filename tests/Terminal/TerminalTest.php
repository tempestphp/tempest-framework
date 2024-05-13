<?php

namespace Tests\Tempest\Console\Terminal;

use Tempest\Console\Console;
use Tempest\Console\Key;
use Tests\Tempest\Console\TestCase;

class TerminalTest extends TestCase
{
    public function test_terminal_rendering(): void
    {
        $this->console
            ->useInteractiveTerminal()
            ->call(function (Console $console) {
                $console->writeln(
                    json_encode(
                        $console->ask('question', ['a', 'b', 'c'], multiple: true),
                    ),
                );
            })
            ->assertContains(<<<TXT
> [ ] a
  [ ] b
  [ ] c
TXT,
            )
            ->assertContains('Press space to select, enter to confirm, ctrl+c to cancel')
            ->input(Key::DOWN)
            ->input(Key::SPACE)
            ->input(Key::DOWN)
            ->input(Key::SPACE)
            ->assertContains(<<<TXT
  [ ] a
  [x] b
> [x] c
TXT,
            )
            ->submit()
            ->assertContains('["b","c"]');
    }
}
