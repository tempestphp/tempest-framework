<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use SensitiveParameter;
use Tempest\Database\Tables\NamingStrategy;
use Tempest\Database\Tables\PluralizedSnakeCaseStrategy;

final class MysqlConfig implements DatabaseConfig
{
    public string $dsn {
        get => sprintf(
            'mysql:host=%s:%s;dbname=%s',
            $this->host,
            $this->port,
            $this->database,
        );
    }

    public DatabaseDialect $dialect {
        get => DatabaseDialect::MYSQL;
    }

    public function __construct(
        #[SensitiveParameter]
        public string $host = 'localhost',
        #[SensitiveParameter]
        public string $port = '3306',
        #[SensitiveParameter]
        public string $username = 'root',
        #[SensitiveParameter]
        public string $password = '',
        #[SensitiveParameter]
        public string $database = 'app',
        public NamingStrategy $namingStrategy = new PluralizedSnakeCaseStrategy(),
    ) {
    }
}
