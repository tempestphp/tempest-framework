<?php

declare(strict_types=1);

namespace Tempest\Console\Output;

use Tempest\Console\OutputBuffer;

final class MemoryOutputBuffer implements OutputBuffer
{
    private array $buffer = [];

    public function __construct()
    {
    }

    public function write(string $contents): void
    {
        $this->buffer[] = $contents;
    }

    public function getBufferWithFormatting(): array
    {
        return $this->buffer;
    }

    public function getBufferWithoutFormatting(): array
    {
        return array_map(
            fn (string $line) => preg_replace('/\x1b\[[0-9;]*m/', '', $line),
            $this->buffer,
        );
    }

    public function asUnformattedString(): string
    {
        return implode('', $this->getBufferWithoutFormatting());
    }

    public function asFormattedString(): string
    {
        return implode('', $this->buffer);
    }

    public function clear(): self
    {
        $this->buffer = [];

        return $this;
    }
}
