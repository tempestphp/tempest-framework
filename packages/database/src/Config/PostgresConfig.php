<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use SensitiveParameter;
use Tempest\Database\Tables\NamingStrategy;
use Tempest\Database\Tables\PluralizedSnakeCaseStrategy;
use UnitEnum;

final class PostgresConfig implements DatabaseConfig
{
    public string $dsn {
        get => $this->buildDsn();
    }

    public DatabaseDialect $dialect {
        get => DatabaseDialect::POSTGRESQL;
    }

    public function __construct(
        #[SensitiveParameter]
        public string $host = '127.0.0.1',
        #[SensitiveParameter]
        public string $port = '5432',
        #[SensitiveParameter]
        public string $username = 'postgres',
        #[SensitiveParameter]
        public ?string $password = null,
        #[SensitiveParameter]
        public string $database = 'app',
        public NamingStrategy $namingStrategy = new PluralizedSnakeCaseStrategy(),
        public null|string|UnitEnum $tag = null,
    ) {}

    private function buildDsn(): string
    {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;user=%s',
            $this->host,
            $this->port,
            $this->database,
            $this->username,
            $this->password,
        );

        if ($this->password !== null) {
            $dsn .= ';password=%s' . $this->password;
        }

        return $dsn;
    }
}
