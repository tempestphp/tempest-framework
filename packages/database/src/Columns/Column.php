<?php

namespace Tempest\Database\Columns;

interface Column
{
    public function name(string $name): static;

    public function getName(): string;

    public function nullable(bool $nullable = true): static;

    public function isNullable(): bool;

    public function default(mixed $default): static;

    public function getDefault(): mixed;
}
