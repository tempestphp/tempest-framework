<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\Console;

final readonly class Hello
{
    public function __construct(
        private Console $console,
    ) {
    }

    // hello:world {input} --flag
    #[ConsoleCommand]
    public function world(string $input): void
    {
        $this->console->info('Hi');
        $this->console->error($input);
    }

    #[ConsoleCommand]
    public function test(
        #[ConsoleArgument]
        ?int $optionalValue = null,
        #[ConsoleArgument(
            name: 'custom-flag',
        )]
        bool $flag = false
    ): void
    {
        $value = $optionalValue ?? 'null';

        $this->console->info("{$value}");

        if ($flag) {
            $this->console->info('flag');
        } else {
            $this->console->info('no-flag');
        }
    }
}
