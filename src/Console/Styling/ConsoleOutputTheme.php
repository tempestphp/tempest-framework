<?php

declare(strict_types=1);

namespace Tempest\Console\Styling;

interface ConsoleOutputTheme
{
    public function format(OutputLine $line): string;
}
