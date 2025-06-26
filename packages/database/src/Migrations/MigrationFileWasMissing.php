<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Exception;

final class MigrationFileWasMissing extends Exception implements MigrationException
{
    public function __construct(string $message = 'Migration file is missing.')
    {
        parent::__construct($message);
    }
}
