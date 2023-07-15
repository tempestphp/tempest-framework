<?php

declare(strict_types=1);

namespace Tempest\Database\Drivers;

use SensitiveParameter;
use Tempest\Interfaces\DatabaseDriver;

final readonly class SQLiteDriver implements DatabaseDriver
{
    public function __construct(
        #[SensitiveParameter] public string $path = 'localhost',
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
}
