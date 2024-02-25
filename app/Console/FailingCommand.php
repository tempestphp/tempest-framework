<?php

declare(strict_types=1);

namespace App\Console;

use Exception;
use Tempest\Console\ConsoleCommand;

final readonly class FailingCommand
{
    #[ConsoleCommand('fail')]
    public function __invoke()
    {
        throw new Exception('A message from the exception');
    }
}
