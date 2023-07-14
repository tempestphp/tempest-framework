<?php

namespace Tempest\Database;

use SensitiveParameter;
use Tempest\Interfaces\DatabaseDriver;

final readonly class SQLiteDriver implements DatabaseDriver
{
    public function __construct(
        #[SensitiveParameter] public string $path = 'localhost',
    ) {}

    public function getDsn(): string {
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
}