<?php

declare(strict_types=1);

namespace Tempest\Console\Output;

use Tempest\Console\OutputBuffer;

final readonly class StdoutOutputBuffer implements OutputBuffer
{
    public function write(string $contents): void
    {
        // Writing to php://stdout will truncate the output at some point,
        // even with flushing. It's the same for /dev/tty or STDOUT.
        // Using PHP's managed buffering seems to be the only way.
        ob_start();
        echo $contents;
        ob_end_flush();
    }
}
