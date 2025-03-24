<?php

declare(strict_types=1);

namespace Tempest\Console\Output;

use Tempest\Console\OutputBuffer;

final readonly class LogOutputBuffer implements OutputBuffer
{
    public function __construct(
        private string $path,
    ) {}

    public function clear(): self
    {
        file_put_contents($this->path, '');

        return $this;
    }

    public function read(): string
    {
        return file_get_contents($this->path);
    }

    public function write(string $contents): void
    {
        $contents = '[' . date('Y-m-d H:i:s') . '] ' . $contents;

        $stdout = fopen($this->path, 'a');

        fwrite($stdout, $contents);

        fclose($stdout);
    }
}
