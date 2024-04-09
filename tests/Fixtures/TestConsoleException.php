<?php

namespace Tests\Tempest\Console\Fixtures;

use Tempest\Console\ConsoleOutput;
use Tempest\Console\Exceptions\ConsoleException;

final class TestConsoleException extends ConsoleException
{
    public function render(ConsoleOutput $output): void
    {
    }
}