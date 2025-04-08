<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Exception;

final class MigrationHashMismatchException extends Exception implements MigrationException
{
    public function __construct(string $message = 'Migration file has been tampered with.')
    {
        parent::__construct($message);
    }
}
