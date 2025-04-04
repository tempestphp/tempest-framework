<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use Tempest\Container\TaggedConfig;
use Tempest\Database\Tables\NamingStrategy;

interface DatabaseConfig extends TaggedConfig
{
    public string $dsn {
        get;
    }

    public NamingStrategy $namingStrategy {
        get;
    }

    public DatabaseDialect $dialect {
        get;
    }

    public ?string $username {
        get;
    }

    public ?string $password {
        get;
    }
}
