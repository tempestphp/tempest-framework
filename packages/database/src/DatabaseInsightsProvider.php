<?php

namespace Tempest\Database;

use Tempest\Core\Insight;
use Tempest\Core\InsightsProvider;
use Tempest\Core\InsightType;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\MysqlConfig;
use Tempest\Database\Config\PostgresConfig;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Support\Arr;
use Tempest\Support\Regex;

use function Tempest\Support\Path\normalize;
use function Tempest\Support\Path\to_relative_path;

final class DatabaseInsightsProvider implements InsightsProvider
{
    public string $name = 'Database';

    public function __construct(
        private readonly DatabaseConfig $databaseConfig,
        private readonly Database $database,
    ) {}

    public function getInsights(): array
    {
        return array_filter([
            'Engine' => $this->getDatabaseEngine(),
            'Version' => $this->getDatabaseVersion(),
            'Path' => $this->getSQLitePath(),
        ]);
    }

    private function getDatabaseEngine(): string
    {
        return match (get_class($this->databaseConfig)) {
            SQLiteConfig::class => 'SQLite',
            PostgresConfig::class => 'PostgreSQL',
            MysqlConfig::class => 'MySQL',
            default => 'Unknown',
        };
    }

    private function getDatabaseVersion(): Insight
    {
        // TODO: support displaying multiple databases, after cache PR
        [$versionQuery, $regex] = match (get_class($this->databaseConfig)) {
            SQLiteConfig::class => ['SELECT sqlite_version() AS version;', '/(?<version>.*)/'],
            PostgresConfig::class => ['SELECT version() AS version;', "/PostgreSQL (?<version>\S+)/"],
            MysqlConfig::class => ['SELECT version() AS version;', '/^(?<version>\d+\.\d+\.\d+)(?:-\w+)?/'],
            default => [null, null],
        };

        if (! $versionQuery) {
            return new Insight('Unknown', InsightType::ERROR);
        }

        try {
            return new Insight(Regex\get_match(
                subject: Arr\get_by_key($this->database->fetchFirst(new Query($versionQuery)), 'version'),
                pattern: $regex,
                match: 'version',
            ));
        } catch (\Throwable $e) {
            return new Insight('Unavailable', InsightType::ERROR);
        }
    }

    private function getSQLitePath(): ?string
    {
        if (! $this->databaseConfig instanceof SQLiteConfig) {
            return null;
        }

        return to_relative_path(getcwd(), normalize($this->databaseConfig->path));
    }
}
