<?php

declare(strict_types=1);

namespace Tempest\Database;

use UnitEnum;

final readonly class QueryExecuted
{
    public function __construct(
        public string $sql,
        public array $bindings,
        public float $durationMs,
        public null|string|UnitEnum $connectionName,
        public bool $failed,
    ) {}
}
