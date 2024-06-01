<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;

final readonly class Hello
{
    public function __construct(
        private Console $console,
    ) {
    }

    // hello:world {input} --flag
    #[ConsoleCommand]
    public function world(string $input)
    {
        $this->console->info('Hi');
        $this->console->error($input);
    }

    // hello:werld {input} --flag
    #[ConsoleCommand]
    public function werdl(string $input)
    {
        $this->console->info('Hi');
        $this->console->error($input);
    }

    #[ConsoleCommand(
        description: 'description',
        aliases: ['t'],
    )]
    public function test(
        #[ConsoleArgument]
        ?int $optionalValue = null,
        bool $flag = false
    ) {
        $value = $optionalValue ?? 'null';

        $this->console->info("{$value}");

        if ($flag) {
            $this->console->info('flag');
        } else {
            $this->console->info('no-flag');
        }
    }
}
