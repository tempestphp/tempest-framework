<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionArguments
{
    public function __construct(
        public string $metadataPath,
        public int $currentIndex,
        public array $words,
    ) {}
}
