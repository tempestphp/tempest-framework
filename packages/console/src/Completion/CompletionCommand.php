<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionCommand
{
    public function __construct(
        public bool $hidden,
        public ?string $description,
        public array $flags,
        private array $flagNameLookup,
    ) {}

    public function resolveFlagName(string $value): ?string
    {
        return $this->flagNameLookup[$value] ?? null;
    }
}
