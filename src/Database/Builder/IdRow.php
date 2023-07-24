<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use Tempest\Interface\TableRow;

final readonly class IdRow implements TableRow
{
    public function __construct(
        private string $name = 'id',
    ) {
    }

    public function getDefinition(): string
    {
        return "{$this->name} INTEGER PRIMARY KEY AUTOINCREMENT";
    }
}
