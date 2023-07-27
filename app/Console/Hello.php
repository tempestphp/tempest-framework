<?php

declare(strict_types=1);

namespace App\Console;

use Tempest\Interface\ConsoleCommand;
use Tempest\Interface\ConsoleOutput;

final readonly class Hello implements ConsoleCommand
{
    public function __construct(
        private ConsoleOutput $output,
    ) {
    }

    // hello:world {input} --flag
    public function world(string $input)
    {
        $this->output->info('Hi');
        $this->output->error($input);
    }

    public function test(?int $optionalValue = null, bool $flag = false)
    {
        $value = $optionalValue ?? 'null';

        $this->output->info("{$value}");

        if ($flag) {
            $this->output->info('flag');
        } else {
            $this->output->info('no-flag');
        }
    }
}
