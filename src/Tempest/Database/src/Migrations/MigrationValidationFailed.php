<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Throwable;

final readonly class MigrationValidationFailed
{
    public function __construct(
        public string $name,
        public Throwable $exception,
    ) {}
}
