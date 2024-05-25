<?php

declare(strict_types=1);

namespace Tempest\Console\Output;

use Closure;

final readonly class TailReader
{
    public function tail(string $path, ?Closure $format = null): void
    {
        $format ??= fn (string $text) => $text;

        $handle = fopen($path, "r");
        fseek($handle, -1, SEEK_END);
        $offset = ftell($handle);

        /** @phpstan-ignore-next-line */
        while (true) {
            fseek($handle, -1, SEEK_END);
            $newOffset = ftell($handle);

            if ($newOffset <= $offset) {
                continue;
            }

            fseek($handle, $offset);

            $output = ltrim(fread($handle, $newOffset - $offset));

            fwrite(STDOUT, $format($output) . PHP_EOL);

            $offset = $newOffset;
        }
    }
}
