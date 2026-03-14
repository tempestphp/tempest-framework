<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Config\DatabaseDialect;
use Throwable;

use function Tempest\Support\Arr\contains;

final class QueryAnalyzer
{
    private ?array $explainResult = null;

    private bool $explainComputed = false;

    public function __construct(
        private(set) readonly QueryExecuted $query,
        private readonly Database $database,
    ) {}

    public function explain(): ?array
    {
        if ($this->explainComputed) {
            return $this->explainResult;
        }

        $this->explainComputed = true;

        if (! $this->query->isSelect()) {
            return null;
        }

        try {
            $this->explainResult = $this->database->fetch(
                new Query($this->getExplainSql(), $this->query->bindings),
            );
        } catch (Throwable) {
            $this->explainResult = null;
        }

        return $this->explainResult;
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

    private function getExplainSql(): string
    {
        return match ($this->database->dialect) {
            DatabaseDialect::SQLITE => "EXPLAIN QUERY PLAN {$this->query->sql}",
            default => "EXPLAIN {$this->query->sql}",
        };
    }
}
