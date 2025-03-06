<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use Tempest\Database\Tables\NamingStrategy;

interface DatabaseConfig
{
    public string $dsn { get; }

    public NamingStrategy $namingStrategy { get; }

    public DatabaseDialect $dialect { get; }

    public ?string $username { get; }

    public ?string $password { get; }
}
