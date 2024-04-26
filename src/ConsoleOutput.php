<?php

declare(strict_types=1);

namespace Tempest\Console;

interface ConsoleOutput
{
    public function write(string $contents): static;

    public function writeln(string $line = ''): static;
}
