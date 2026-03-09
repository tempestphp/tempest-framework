<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionCandidate
{
    public function __construct(
        public string $value,
        public ?string $display = null,
    ) {}
}
