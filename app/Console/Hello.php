<?php

declare(strict_types=1);

namespace App\Console;

use Tempest\Interface\ConsoleCommand;
use Tempest\Interface\ConsoleOutput;

// Get argument from CLI
// Map argument to command route
// Execute the command and return data
final readonly class Hello implements ConsoleCommand
{
    public function __construct(
        private ConsoleOutput $output
    ) {}

    // hello:world {input} --flag
    public function world(string $input)
    {
        $this->output->info('Hi');
        $this->output->error($input);
    }
}
