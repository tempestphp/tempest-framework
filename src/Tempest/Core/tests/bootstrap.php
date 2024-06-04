<?php

declare(strict_types=1);

use Tempest\Highlight\Themes\TerminalStyle;

$files = glob(__DIR__ . '/../src/Discovery/*.cache.php');

foreach ($files as $file) {
    unlink($file);
}

fwrite(
    STDOUT,
    TerminalStyle::BOLD(TerminalStyle::FG_WHITE(TerminalStyle::BG_BLUE(" Discovery cache cleared "))) . PHP_EOL
);
