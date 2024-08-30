<?php

declare(strict_types=1);

namespace Tempest\Database\Connections;

use SensitiveParameter;
use Tempest\Database\DatabaseConnection;
use Tempest\Database\DatabaseDialect;

final readonly class SQLiteConnection implements DatabaseConnection
{
    public function __construct(
        #[SensitiveParameter]
        public string $path = 'localhost',
    ) {
    }

    public function getDsn(): string
    {
        return "sqlite:{$this->path}";
    }

    public function getUsername(): ?string
    {
        return null;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function dialect(): DatabaseDialect
    {
        return DatabaseDialect::SQLITE;
    }
}
