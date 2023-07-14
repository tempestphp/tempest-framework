<?php

namespace Tempest\Database\TableBuilder;

use Tempest\Interfaces\TableRow;

final readonly class TextRow implements TableRow
{
    public function __construct(
        private string $name,
        private bool $nullable = false,
    ) {}

    public function getDefinition(): string
    {
        $nullable = $this->nullable ? '' : 'NOT NULL';

        return "{$this->name} TEXT {$nullable}";
    }
}