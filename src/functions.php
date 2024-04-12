<?php

declare(strict_types=1);

function ld(mixed ...$input): void
{
    file_put_contents(__DIR__ . '/../console.log', '---' . PHP_EOL, FILE_APPEND);

    foreach ($input as $key => $item) {
        $output = $key . PHP_EOL . ' > '  . var_export($item, true) . PHP_EOL;
        file_put_contents(__DIR__ . '/../console.log', $output, FILE_APPEND);
    }
}
