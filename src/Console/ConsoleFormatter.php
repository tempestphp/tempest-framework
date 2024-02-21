<?php

declare(strict_types=1);

namespace Tempest\Console;

interface ConsoleFormatter
{
    public function format(string $text, ConsoleStyle ...$styles): string;
}
