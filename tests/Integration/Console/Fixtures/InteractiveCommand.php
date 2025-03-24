<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Validation\Rules\Count;
use Tempest\Validation\Rules\Email;
use Tempest\Validation\Rules\Length;

final readonly class InteractiveCommand
{
    use HasConsole;

    public function __construct(
        private Console $console,
    ) {}

    #[ConsoleCommand('interactive:enum')]
    public function enum(): void
    {
        $this->ask(
            question: 'Pick one',
            options: MyEnum::class,
            default: MyEnum::OTHER,
        );

        $this->console->success('Done');
    }

    #[ConsoleCommand('interactive:validation')]
    public function validation(): void
    {
        $a = $this->console->ask('a', validation: [new Length(min: 2), new Length(max: 2)]);
        $b = $this->console->ask('b', validation: [new Email()]);

        $this->console->success("{$a} {$b}");
    }

    #[ConsoleCommand('interactive:confirm')]
    public function confirmCommand(): void
    {
        $confirm = $this->console->confirm('abc', true);

        $this->console->writeln($confirm ? 'yes' : 'no');
    }

    #[ConsoleCommand('interactive:password')]
    public function passwordCommand(): void
    {
        $password = $this->console->password(confirm: true);

        $this->console->writeln($password);
    }

    #[ConsoleCommand('interactive:single')]
    public function single(): void
    {
        $result = $this->console->ask(
            'Pick one option',
            [
                'a',
                'b',
                'c',
            ],
            default: 1,
            multiple: true,
        );

        $result = json_encode($result);

        $this->console->writeln("You picked <em>{$result}</em>");
    }

    #[ConsoleCommand('interactive:single_without_default')]
    public function single_without_default(): void
    {
        $result = $this->console->ask(
            'Pick one option',
            [
                'a',
                'b',
                'c',
            ],
            multiple: true,
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
    public function interactiveAsk(): void
    {
        $answer = $this->console->ask('Hello?');

        $this->console->success($answer);
    }

    #[ConsoleCommand('interactive:progress')]
    public function progress(): void
    {
        $result = $this->console->progressBar(
            data: array_fill(0, 10, 'a'),
            handler: function ($i) {
                usleep(100000);

                return $i . $i;
            },
        );

        $this->console->writeln(json_encode($result, JSON_PRETTY_PRINT));
    }

    #[ConsoleCommand('interactive:search')]
    public function searchCommand(): void
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
            },
        );

        $this->console->success("Hello {$result}");
    }
}
