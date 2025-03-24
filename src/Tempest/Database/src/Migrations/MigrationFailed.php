<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Throwable;

final readonly class MigrationFailed
{
    public function __construct(
        public string $name,
        public Throwable $exception,
    ) {}
}
