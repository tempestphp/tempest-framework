<?php

declare(strict_types=1);

namespace App\Console;

use Tempest\Console\Components\QuestionComponent;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final readonly class InteractiveCommand
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand('interactive')]
    public function __invoke(): void
    {
        $result = $this->console->component(new QuestionComponent(
            'Pick an option, which is best?',
            [
                'interfaces + final',
                'abstract classes + extend',
                'I don\'t really care…',
            ],
        ));

        $this->console->writeln("You picked <em>{$result}</em>");
    }
}
