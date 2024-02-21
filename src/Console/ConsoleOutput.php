<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Container\InitializedBy;

#[InitializedBy(ConsoleOutputInitializer::class)]
interface ConsoleOutput
{
    public function writeln(string $line, ConsoleStyle ...$styles): void;

    public function info(string $line): void;

    public function error(string $line): void;

    public function success(string $line): void;
}
