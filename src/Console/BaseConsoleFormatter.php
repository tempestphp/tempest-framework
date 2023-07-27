<?php

declare(strict_types=1);

namespace Tempest\Console;

trait BaseConsoleFormatter
{
    public function format(string $text, ConsoleStyle ...$styles): string
    {
        $style = implode('', array_map(
            fn (ConsoleStyle $style) => ConsoleStyle::ESC->value . $style->value,
            $styles
        ));

        return "{$style}{$text}";
    }
}
