<?php

declare(strict_types=1);

namespace Tests\Tempest;

use Tempest\Console\ConsoleFormatter;
use Tempest\Console\ConsoleStyle;

final readonly class TestConsoleFormatter implements ConsoleFormatter
{
    public function format(string $text, ConsoleStyle ...$styles): string
    {
        return $text;
    }
}
