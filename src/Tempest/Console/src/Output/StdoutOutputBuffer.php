<?php

declare(strict_types=1);

namespace Tempest\Console\Output;

use Tempest\Console\OutputBuffer;

final readonly class StdoutOutputBuffer implements OutputBuffer
{
    public function write(string $contents): void
    {
        // Using `/dev/tty` prevents truncation when
        // there are too many escape codes.
        if ($tty = @fopen('/dev/tty', 'w')) {
            fwrite($tty, $contents);
            fclose($tty);

            return;
        }

        fwrite(STDOUT, $contents);
    }
}
