<?php

namespace Tempest\Console\Exceptions;

use Tempest\Console\Console;

final class InterruptException extends ConsoleException
{
    public function render(Console $console): void
    {
        $console->writeln('<error>Canceled</error>');
    }
}