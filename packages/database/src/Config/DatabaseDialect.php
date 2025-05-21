<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use PDOException;

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

    public function isTableNotFoundError(PDOException $exception): bool
    {
        return match ($this) {
            self::MYSQL => $exception->getCode() === '42S02' && str_contains($exception->getMessage(), 'table'),
            self::SQLITE => $exception->getCode() === 'HY000' && str_contains($exception->getMessage(), 'table'),
            self::POSTGRESQL => $exception->getCode() === '42P01' && str_contains($exception->getMessage(), 'relation'),
        };
    }
}
