<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

final class MissingDatabaseConfig extends DatabaseException
{
    public function __construct()
    {
        parent::__construct('No database config found.');
    }
}
