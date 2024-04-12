<?php

declare(strict_types=1);

namespace App\Console;

use Tempest\Console\Components\MultipleChoiceComponent;
use Tempest\Console\Components\ProgressBarComponent;
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
        //        $result = $this->console->component(new MultipleChoiceComponent(
        //            'Pick an option, which is best?',
        //            [
        //                'interfaces + final',
        //                'abstract classes + extend',
        ////                'I don\'t really care…',
        ////                'interfaces + final',
        ////                'abstract classes + extend',
        //            ],
        //        ));
        //
        //        $result = json_encode($result);
        //
        //        $this->console->writeln("You picked <em>{$result}</em>");

        //        $result = $this->console->writeln()->ask('Next question:');
        //
        //        $this->console->writeln("You wrote <em>{$result}</em>");

        $result = $this->console->component(
            new ProgressBarComponent(
                data: array_fill(0, 10, 'a'),
                handler: function ($i) {
                    usleep(100000);

                    return $i;
                },
            ),
        );

        $this->console->writeln('Done!');
    }
}
