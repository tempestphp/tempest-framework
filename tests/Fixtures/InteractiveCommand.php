<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Validation\Rules\Count;
use Tempest\Validation\Rules\Email;
use Tempest\Validation\Rules\Length;

final readonly class InteractiveCommand
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand('interactive:validation')]
    public function validation(): void
    {
        $a = $this->console->ask('a', validation: [new Length(min: 2), new Length(max: 2)]);
        $b = $this->console->ask('b', validation: [new Email()]);

        $this->console->success("$a $b");
    }

    #[ConsoleCommand('interactive:confirm')]
    public function confirm(): void
    {
        $this->console->confirm('abc');
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
    }

    #[ConsoleCommand('interactive:multiple')]
    public function multiple(): void
    {
        $result = $this->console->ask(
            question: 'Pick several',
            options: ['a', 'b', 'c'],
            multiple: true,
            validation: [new Count(min: 1)],
        );

        $result = json_encode($result);

        $this->console->writeln("You picked <em>{$result}</em>");
    }

    #[ConsoleCommand('interactive:ask')]
    public function ask(): void
    {
        $this->console->ask('Hello?');
    }

    #[ConsoleCommand('interactive:progress')]
    public function progress(): void
    {
        $this->console->progressBar(
            data: array_fill(0, 10, 'a'),
            handler: function ($i) {
                usleep(100000);

                return $i;
            },
        );
    }

    #[ConsoleCommand('interactive:search')]
    public function search(): void
    {
        $data = ['Brent', 'Paul', 'Aidan', 'Roman'];

        $result = $this->console->search(
            'Search',
            function (string $query) use ($data): array {
                if ($query === '') {
                    return [];
                }

                return array_filter(
                    $data,
                    fn (string $name) => str_contains(strtolower($name), strtolower($query)),
                );
            }
        );

        $this->console->success("Hello {$result}");
    }
}
