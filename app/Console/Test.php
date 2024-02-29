<?php

declare(strict_types=1);

namespace App\Console;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleInput;
use Tempest\Console\ConsoleOutput;

final readonly class Test
{
    public function __construct(
        private ConsoleOutput $output,
        private ConsoleInput $input,
    ) {
    }

    #[ConsoleCommand]
    public function test()
    {
        $this->input->confirm('yes or no?');
    }
}
