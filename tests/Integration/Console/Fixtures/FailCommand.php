<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Exception;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;

final readonly class FailCommand
{
    #[ConsoleCommand('fail')]
    public function __invoke(
        string $input = 'default',
        #[ConsoleArgument(aliases: ['-v'])]
        bool $verbose = false, // @mago-expect best-practices/no-unused-parameter
    ): void {
        failing_function($input);
    }
}

function failing_function(string $string): void
{
    throw new Exception("A message from the exception {$string}");
}
