<?php

declare(strict_types=1);

namespace Tempest\Console\Output;

use Tempest\Console\OutputBuffer;

final readonly class StdoutOutputBuffer implements OutputBuffer
{
    public function write(string $contents): void
    {
        $stdout = fopen('php://stdout', 'w');

        fwrite($stdout, $contents);

        fclose($stdout);
    }
}
