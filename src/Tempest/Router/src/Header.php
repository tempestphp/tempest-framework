<?php

declare(strict_types=1);

namespace Tempest\Router;

final class Header
{
    public function __construct(
        public string $name,
        /** @var array<array-key, mixed> $values */
        public array $values = [],
    ) {}

    public function add(mixed $value): void
    {
        $this->values[] = $value;
    }
}
