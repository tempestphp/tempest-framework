<?php

declare(strict_types=1);

namespace App\Console;

use Exception;
use Tempest\Console\ConsoleCommand;

final readonly class FailCommand
{
    #[ConsoleCommand('fail')]
    public function __invoke(string $input)
    {
        failingFunction($input);
    }
}

function failingFunction(string $string)
{
    throw new Exception("A message from the exception {$string}");
}
