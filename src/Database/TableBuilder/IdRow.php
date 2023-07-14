<?php

namespace Tempest\Database\TableBuilder;

use Tempest\Interfaces\TableRow;

final readonly class IdRow implements TableRow
{
    public function __construct(
        private string $name = 'id',
    ) {}

    public function getDefinition(): string
    {
        return "{$this->name} INTEGER PRIMARY KEY AUTOINCREMENT";
    }
}