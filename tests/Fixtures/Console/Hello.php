<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use Tempest\Console\Console;
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

    #[ConsoleCommand]
    public function test(?int $optionalValue = null, bool $flag = false)
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
