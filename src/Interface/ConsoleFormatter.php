<?php

namespace Tempest\Interface;

use Tempest\Console\ConsoleStyle;

interface ConsoleFormatter
{
    public function format(string $text, ConsoleStyle ...$styles): string;
}