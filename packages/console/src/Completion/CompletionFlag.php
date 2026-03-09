<?php

declare(strict_types=1);

namespace Tempest\Console\Completion;

final readonly class CompletionFlag
{
    public function __construct(
        public string $name,
        public string $flag,
        public array $aliases,
        public ?string $description,
        public array $valueOptions,
        public bool $repeatable,
    ) {}
}
