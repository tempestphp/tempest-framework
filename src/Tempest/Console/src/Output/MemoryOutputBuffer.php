<?php

declare(strict_types=1);

namespace Tempest\Console\Output;

use Tempest\Console\OutputBuffer;
use Tempest\Highlight\Themes\TerminalStyle;

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
        $pattern = array_map(
            fn (TerminalStyle $consoleStyle) => TerminalStyle::ESC->value . $consoleStyle->value,
            TerminalStyle::cases(),
        );

        return array_map(
            fn (string $line) => str_replace($pattern, '', $line),
            $this->buffer,
        );
    }

    public function asUnformattedString(): string
    {
        return implode('', $this->getBufferWithoutFormatting());
    }

    public function asFormattedString(): string
    {
        return implode('', $this->getBufferWithFormatting());
    }

    public function clear(): self
    {
        $this->buffer = [];

        return $this;
    }
}
