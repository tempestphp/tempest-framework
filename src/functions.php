<?php

declare(strict_types=1);

use Tempest\Highlight\Themes\TerminalStyle;

function ld(mixed ...$input): void
{
    //    file_put_contents(__DIR__ . '/../console.log', PHP_EOL . TerminalStyle::FG_BLUE('['. date('Y-m-d H:i:s') . ']') . PHP_EOL, FILE_APPEND);

    foreach ($input as $key => $item) {
        if ($item instanceof Stringable) {
            $value = (string)$item;
        } else {
            $value = var_export($item, true);
        }

        $output = TerminalStyle::BG_BLUE(TerminalStyle::FG_WHITE(' ' . $key . ' ')) . ' ' . $value . PHP_EOL . PHP_EOL;
        file_put_contents(__DIR__ . '/../console.log', $output, FILE_APPEND);
    }
}
