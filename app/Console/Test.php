<?php

namespace App\Console;

use Tempest\Interface\ConsoleCommand;
use Tempest\Interface\ConsoleInput;
use Tempest\Interface\ConsoleOutput;

final readonly class Test implements ConsoleCommand
{
    public function __construct(
        private ConsoleOutput $output,
        private ConsoleInput $input,
    ) {}

    public function test()
    {
        dump($this->input->confirm('yes or no?'));
    }
}