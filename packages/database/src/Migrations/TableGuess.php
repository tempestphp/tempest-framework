<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

final readonly class TableGuess
{
    public function __construct(
        public string $table,
        public bool $isCreate,
    ) {}
}
