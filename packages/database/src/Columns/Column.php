<?php

namespace Tempest\Database\Columns;

interface Column
{
    public string $name { get; }
    public bool $nullable { get; }
    public mixed $default { get; }
    public bool $unique { get; }
    public bool $primaryKey { get; }

    public function name(string $name): static;

    public function nullable(bool $nullable = true): static;

    public function default(mixed $default): static;
}
