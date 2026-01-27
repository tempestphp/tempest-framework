<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use function Tempest\Support\Random\uuid;

final class Uuidv7PrefixStrategy implements MigrationNamingStrategy
{
    public function generatePrefix(): string
    {
        return uuid();
    }
}
