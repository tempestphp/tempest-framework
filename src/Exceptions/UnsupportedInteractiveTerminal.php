<?php

namespace Tempest\Console\Exceptions;

use Tempest\Console\Components\InteractiveComponent;
use Tempest\Console\Console;

final class UnsupportedInteractiveTerminal extends ConsoleException
{
    public function __construct(InteractiveComponent $component)
    {
        $className = $component::class;

        parent::__construct("Could not start an interactive terminal to render {$className}, you need `stty` and `tput` installed.");
    }

    public function render(Console $console): void
    {
        $console->error($this->message);
    }
}