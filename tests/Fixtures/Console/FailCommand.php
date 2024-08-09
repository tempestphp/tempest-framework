<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use Exception;
use Tempest\Console\ConsoleCommand;

final readonly class FailCommand
{
    #[ConsoleCommand('fail')]
    public function __invoke(string $input): void
    {
        failingFunction($input);
    }
}

function failingFunction(string $string): void
{
    throw new Exception("A message from the exception {$string}");
}
