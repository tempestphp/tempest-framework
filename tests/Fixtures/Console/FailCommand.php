<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use Exception;
use Tempest\Console\ConsoleCommand;

final readonly class FailCommand
{
    #[ConsoleCommand('fail')]
    public function failWithException(string $input, bool $error = false): void
    {
        if ($error) {
            trigger_error('Error message', E_USER_ERROR);
        } else {
            failingFunction($input);
        }
    }
}

function failingFunction(string $string): void
{
    throw new Exception("A message from the exception {$string}");
}
