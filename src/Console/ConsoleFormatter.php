<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Container\InitializedBy;

#[InitializedBy(ConsoleFormatterInitializer::class)]
interface ConsoleFormatter
{
    public function format(string $text, ConsoleStyle ...$styles): string;
}
