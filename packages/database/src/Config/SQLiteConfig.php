<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use SensitiveParameter;
use Tempest\Database\Tables\NamingStrategy;
use Tempest\Database\Tables\PluralizedSnakeCaseStrategy;
use UnitEnum;

final class SQLiteConfig implements DatabaseConfig
{
    public string $dsn {
        get => sprintf(
            'sqlite:%s',
            $this->path,
        );
    }

    public ?string $username {
        get => null;
    }

    public ?string $password {
        get => null;
    }

    public DatabaseDialect $dialect {
        get => DatabaseDialect::SQLITE;
    }

    public function __construct(
        #[SensitiveParameter]
        public string $path = 'localhost',
        public NamingStrategy $namingStrategy = new PluralizedSnakeCaseStrategy(),
        public null|string|UnitEnum $tag = null,
    ) {}
}
