<?php

declare(strict_types=1);

namespace Tempest\Console\Output;

final readonly class StdoutOutputBuffer implements OutputBuffer
{
    public function write(string $contents): void
    {
        $stdout = fopen('php://stdout', 'w');

        fwrite($stdout, $contents);

        fclose($stdout);
    }
}
