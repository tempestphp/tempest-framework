<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use Tempest\Container\HasTag;
use Tempest\Database\Tables\NamingStrategy;

interface DatabaseConfig extends HasTag
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
