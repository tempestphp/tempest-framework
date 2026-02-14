<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

/**
 * Generates a date-based prefix for migration names.
 */
final class DatePrefixStrategy implements MigrationNamingStrategy
{
    public function __construct(
        private bool $useTime = false,
    ) {}

    public function generatePrefix(): string
    {
        return date($this->useTime ? 'Y-m-d_His' : 'Y-m-d');
    }
}
