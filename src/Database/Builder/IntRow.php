<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use Tempest\Interface\TableRow;

final readonly class IntRow implements TableRow
{
    public function __construct(
        private string $name,
        private bool $nullable = false,
    ) {
    }

    public function getDefinition(): string
    {
        $nullable = $this->nullable ? '' : 'NOT NULL';

        return "{$this->name} INT {$nullable}";
    }
}
