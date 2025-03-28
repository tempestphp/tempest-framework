<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Exception;

final class TableNotFoundException extends Exception implements MigrationException
{
    public function __construct(
        string $message = 'Migrations table does not exist. Nothing to roll back.',
    ) {
        parent::__construct($message);
    }
}
