<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Support\Random;

/**
 * Generates a UUIDv7 prefix for migration names.
 */
final class Uuidv7PrefixStrategy implements MigrationNamingStrategy
{
    public function generatePrefix(): string
    {
        return Random\uuid();
    }
}
