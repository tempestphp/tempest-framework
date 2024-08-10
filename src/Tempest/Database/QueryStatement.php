<?php

declare(strict_types=1);

namespace Tempest\Database;

use Stringable;

final class QueryStatement implements Stringable
{
    private string $table; // @phpstan-ignore-line

    public function __construct(
        private readonly DatabaseDriver $driver, // @phpstan-ignore-line
        private array                   $query = [],
    ) {
    }

    public function __toString(): string
    {
        return implode(', ', $this->query);
    }

    public function toQuery(): Query
    {
        return new Query((string) $this);
    }
}
