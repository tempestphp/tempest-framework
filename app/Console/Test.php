<?php

namespace App\Console;

use Tempest\Console\ConsoleCommand;
use Tempest\Interface\ConsoleInput;
use Tempest\Interface\ConsoleOutput;

final readonly class Test
{
    public function __construct(
        private ConsoleOutput $output,
        private ConsoleInput $input,
    ) {}

    #[ConsoleCommand]
    public function test()
    {
        dump($this->input->confirm('yes or no?'));
    }
}