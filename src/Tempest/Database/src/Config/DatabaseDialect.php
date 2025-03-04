<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

enum DatabaseDialect: string
{
    case SQLITE = 'sqlite';
    case MYSQL = 'mysql';
    case POSTGRESQL = 'pgsql';

    public function tableNotFoundCode(): string
    {
        return match ($this) {
            self::MYSQL => '42S02',
            self::POSTGRESQL => '42P01',
            self::SQLITE => 'HY000',
        };
    }
}
