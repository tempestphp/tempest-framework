<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final readonly class TestCommand
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand('test:foo')]
    public function __invoke(): void
    {
        //        if (! $this->console->confirm('Are you sure you want to continue?')) {
        //            return;
        //        }

        //        $name = $this->console->ask("What's your name?");

        $this->console->writeln('<comment>Comment
asd</comment>');
        //        $email = $this->console->ask("What's your email?", validation: [new Email()]);
        //
        //        $this->console->writeln()->writeln("Welcome, <{$email}>");
    }

    #[ConsoleCommand]
    public function test(): void
    {
        $this->console->confirm('yes or no?');
    }
}
