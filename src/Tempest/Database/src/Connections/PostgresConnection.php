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
        public string $host = '127.0.0.1',
        #[SensitiveParameter]
        public string $port = '5432',
        #[SensitiveParameter]
        public string $username = '',
        #[SensitiveParameter]
        public string $password = '',
        #[SensitiveParameter]
        public string $database = 'postgres',
    ) {
    }

    public function getDsn(): string
    {
        //DATABASE_URL="postgresql://postgres:postgres@postgres:5432/mydb?schema=public"

        return sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s',
            $this->host,
            $this->port,
            $this->database,
            $this->username,
            $this->password,
        );
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
