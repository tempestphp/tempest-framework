<?php

declare(strict_types=1);

namespace Tempest\View;

final readonly class CompiledView
{
    /**
     * @param array<int, array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int}> $lineMap
     */
    public function __construct(
        public string $content,
        public ?string $sourcePath,
        public array $lineMap,
    ) {}
}
