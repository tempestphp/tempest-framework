<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use Tempest\Database\Exceptions\QueryWasInvalid;

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

    public function isTableNotFoundError(QueryWasInvalid $queryWasInvalid): bool
    {
        $pdoException = $queryWasInvalid->pdoException;

        return match ($this) {
            self::MYSQL => $pdoException->getCode() === '42S02' && str_contains($pdoException->getMessage(), 'table'),
            self::SQLITE => $pdoException->getCode() === 'HY000' && str_contains($pdoException->getMessage(), 'table'),
            self::POSTGRESQL => $pdoException->getCode() === '42P01' && str_contains($pdoException->getMessage(), 'relation'),
        };
    }
}
