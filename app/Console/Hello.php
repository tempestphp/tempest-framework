<?php

declare(strict_types=1);

namespace App\Console;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;

final readonly class Hello
{
    public function __construct(
        private ConsoleOutput $output,
    ) {
    }

    // hello:world {input} --flag
    #[ConsoleCommand]
    public function world(string $input)
    {
        $this->output->info('Hi');
        $this->output->error($input);
    }

    #[ConsoleCommand(
        description: 'description',
        aliases: ['t'],
    )]
    public function test(
        #[ConsoleArgument(
            help: 'The path to the file',
            aliases: ['ov']
        )]
        ?int $optionalValue = null,
        bool $flag = false
    ) {
        $value = $optionalValue ?? 'null';

        $this->output->info("{$value}");

        if ($flag) {
            $this->output->info('flag');
        } else {
            $this->output->info('no-flag');
        }
    }
}
