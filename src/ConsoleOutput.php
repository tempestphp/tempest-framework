<?php

declare(strict_types=1);

namespace Tempest\Console;

interface ConsoleOutput
{
    public function write(string $line): void;

    public function writeln(string $line): void;

    public function info(string $line): void;

    public function error(string $line): void;

    public function success(string $line): void;
}
