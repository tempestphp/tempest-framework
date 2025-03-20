<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Exception;

final class MigrationException extends Exception
{
    public static function noTable(): self
    {
        return new self('Migrations table does not exist. Nothing to roll back.');
    }

    public static function hashMismatch(): self
    {
        return new self('Migration hash mismatch.');
    }
}
