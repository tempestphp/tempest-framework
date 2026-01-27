<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

interface MigrationNamingStrategy
{
    /**
     * Generate the prefix for a migration name.
     *
     * This is used to create sortable, unique migration identifiers.
     * For example: '2026-01-27' or '20260127143022'.
     */
    public function generatePrefix(): string;
}
