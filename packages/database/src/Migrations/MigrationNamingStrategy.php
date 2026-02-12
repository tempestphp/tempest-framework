<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

/**
 * Represents a strategy for naming database migrations. This is used to create sortable, unique migration identifiers.
 */
interface MigrationNamingStrategy
{
    /**
     * Generates the prefix for a migration name.
     */
    public function generatePrefix(): string;
}
