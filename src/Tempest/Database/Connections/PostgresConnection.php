<?php

declare(strict_types=1);

namespace Tempest\Database\Connections;

use SensitiveParameter;
use Tempest\Database\DatabaseConnection;
use Tempest\Database\DatabaseDialect;

final class PostgresConnection implements DatabaseConnection
{
    public function __construct(
        #[SensitiveParameter]
        public string $host = 'localhost',
        #[SensitiveParameter]
        public string $port = '5432',
        #[SensitiveParameter]
        public string $username = 'postgres',
        #[SensitiveParameter]
        public string $password = '',
        #[SensitiveParameter]
        public string $database = 'app',
    ) {
    }

    public function getDsn(): string
    {
        return "postgresql:{$this->host}:{$this->port}/{$this->database}";
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function dialect(): DatabaseDialect
    {
        return DatabaseDialect::POSTGRESQL;
    }
}
