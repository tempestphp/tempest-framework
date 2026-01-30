<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Config\DatabaseDialect;
use Throwable;
use UnitEnum;

use function Tempest\Container\get;
use function Tempest\Support\Arr\contains;
use function Tempest\Support\Str\before_first;
use function Tempest\Support\Str\to_upper_case;

final class QueryExecuted
{
    private ?array $explainResult = null;
    private bool $explainComputed = false;

    public string $queryType {
        get => to_upper_case(before_first(trim($this->sql), ' '));
    }

    public function __construct(
        public readonly string $sql,
        public readonly array $bindings,
        public readonly float $durationMs,
        public readonly null|string|UnitEnum $connectionName,
        public readonly bool $failed,
    ) {}

    public function explain(): ?array
    {
        if ($this->explainComputed) {
            return $this->explainResult;
        }

        $this->explainComputed = true;

        if (! $this->isSelect()) {
            return null;
        }

        try {
            $db = get(Database::class);
            $this->explainResult = $db->fetch(
                new Query($this->getExplainSql($db->dialect), $this->bindings),
            );
        } catch (Throwable) {
            $this->explainResult = null;
        }

        return $this->explainResult;
    }

    private function getExplainSql(DatabaseDialect $dialect): string
    {
        return match ($dialect) {
            DatabaseDialect::SQLITE => "EXPLAIN QUERY PLAN {$this->sql}",
            default => "EXPLAIN {$this->sql}",
        };
    }

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

    public function usesFullTableScan(): bool
    {
        $explain = $this->explain();

        if ($explain === null) {
            return false;
        }

        return contains($explain, static function (array $row): bool {
            $isFullScanType = isset($row['type']) && strtoupper($row['type']) === 'ALL';
            $hasScanInDetail = isset($row['detail']) && str_contains(strtoupper($row['detail']), 'SCAN');

            return $isFullScanType || $hasScanInDetail;
        });
    }

    public function getRowsExamined(): int
    {
        $explain = $this->explain();

        if ($explain === null) {
            return 0;
        }

        $total = 0;

        foreach ($explain as $row) {
            if (isset($row['rows'])) {
                $total += (int) $row['rows'];
            }

            if (isset($row['detail']) && preg_match('/~(\d+) rows/i', $row['detail'], $matches)) {
                $total += (int) $matches[1];
            }
        }

        return $total;
    }

    public function usesIndex(): bool
    {
        return $this->getIndexUsed() !== null;
    }

    public function getIndexUsed(): ?string
    {
        $explain = $this->explain();

        if ($explain === null) {
            return null;
        }

        foreach ($explain as $row) {
            if (isset($row['key']) && $row['key'] !== '') {
                return $row['key'];
            }

            if (isset($row['detail'])) {
                if (preg_match('/USING INDEX (\S+)/i', $row['detail'], $matches)) {
                    return $matches[1];
                }

                if (preg_match('/USING (INTEGER )?PRIMARY KEY/i', $row['detail'])) {
                    return 'PRIMARY KEY';
                }
            }
        }

        return null;
    }
}
