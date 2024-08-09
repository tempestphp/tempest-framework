<?php

declare(strict_types=1);

namespace Tempest\Database;

enum DatabaseDialect: string
{
    case SQLITE = 'sqlite';
    case MYSQL = 'mysql';
    case POSTGRESQL = 'pgsql';
}
