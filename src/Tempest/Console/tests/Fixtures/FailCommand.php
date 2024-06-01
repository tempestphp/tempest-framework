<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Exception;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;

final readonly class FailCommand
{
    #[ConsoleCommand('fail')]
    public function __invoke(string $input = 'default', #[ConsoleArgument(aliases: ['-v'])] bool $verbose = false): void
    {
        failingFunction($input);
    }
}

function failingFunction(string $string)
{
    throw new Exception("A message from the exception {$string}");
}
