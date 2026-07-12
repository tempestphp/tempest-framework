<?php

declare(strict_types=1);

namespace Tempest\Database;

use UnitEnum;

use function Tempest\Support\Str\before_first;
use function Tempest\Support\Str\to_upper_case;

final class QueryExecuted
{
    public string $queryType {
        get => to_upper_case(before_first(trim($this->sql), ' '));
    }

    public function __construct(
        public string $sql,
        public array $bindings,
        public float $durationMs,
        public string|UnitEnum|null $connectionName,
        public bool $failed,
    ) {}

    public function isSlow(float $thresholdMs = 100.0): bool
    {
        return $this->durationMs > $thresholdMs;
    }

    public function isSelect(): bool
    {
        return $this->queryType === 'SELECT';
    }

    public function isInsert(): bool
    {
        return $this->queryType === 'INSERT';
    }

    public function isUpdate(): bool
    {
        return $this->queryType === 'UPDATE';
    }

    public function isDelete(): bool
    {
        return $this->queryType === 'DELETE';
    }
}
