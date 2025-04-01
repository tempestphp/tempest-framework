<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\Console;
use Tempest\Console\InteractiveConsoleComponent;

final class UnsupportedComponent extends ConsoleException
{
    public function __construct(InteractiveConsoleComponent $component)
    {
        $className = $component::class;

        parent::__construct("Could not start an interactive terminal to render {$className}, you need `stty` and `tput` installed.");
    }

    public function render(Console $console): void
    {
        $console->writeln();
        $console->error($this->message);
    }
}
