<?php

declare(strict_types=1);

namespace App\Console;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final readonly class InteractiveCommand
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand('interactive:password')]
    public function password(): void
    {
        $password = $this->console->password(confirm: true);

        $this->console->writeln($password);
    }

    #[ConsoleCommand('interactive:option')]
    public function option(): void
    {
        $result = $this->console->ask(
            'Pick one option',
            [
                'a', 'b', 'c',
            ],
        );

        $result = json_encode($result);

        $this->console->writeln("You picked <em>{$result}</em>");

        //        $result = $this->console->writeln()->ask('Next question:');
        //
        //        $this->console->writeln("You wrote <em>{$result}</em>");

        //        $result = $this->console->progressBar(
        //            data: array_fill(0, 10, 'a'),
        //            handler: function ($i) {
        //                usleep(100000);
        //
        //                return $i;
        //            },
        //        );
    }

    #[ConsoleCommand('interactive:ask')]
    public function ask()
    {
        $this->console->ask('Hello?');
    }
}
