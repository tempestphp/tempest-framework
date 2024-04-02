<?php

declare(strict_types=1);

namespace Tempest\Console\Styling;

final class OutputLine
{
    public function __construct(
        public readonly string $line,
        public readonly LineType $type,
    ) {

    }

    public function format(ConsoleOutputTheme $theme): string
    {
        return $theme->format(
            $this,
        );
    }
}
