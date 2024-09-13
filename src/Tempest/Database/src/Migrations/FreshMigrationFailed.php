<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Throwable;

final readonly class FreshMigrationFailed
{
    public function __construct(public Throwable $throwable)
    {
    }
}
