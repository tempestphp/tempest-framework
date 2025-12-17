<?php

declare(strict_types=1);

namespace Tempest\Debug\Stacktrace;

final readonly class CodeSnippet
{
    /**
     * @param array<int,string> $lines
     */
    public function __construct(
        public array $lines,
        public int $highlightedLine,
    ) {}

    public function getStartLine(): int
    {
        return array_key_first($this->lines) ?? 0;
    }

    public function getEndLine(): int
    {
        return array_key_last($this->lines) ?? 0;
    }
}
