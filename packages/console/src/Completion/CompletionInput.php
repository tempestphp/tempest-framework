<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionInput
{
    public function __construct(
        public array $words,
        public int $currentIndex,
    ) {}
}
