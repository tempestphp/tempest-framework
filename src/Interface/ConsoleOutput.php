<?php

declare(strict_types=1);

namespace Tempest\Interface;

use Tempest\Console\ConsoleStyle;

interface ConsoleOutput
{
    public function writeln(string $line, ConsoleStyle ...$styles): void;

    public function info(string $line): void;

    public function error(string $line): void;

    public function success(string $line): void;
}
