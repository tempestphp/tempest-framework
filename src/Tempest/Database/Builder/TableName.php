<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

final readonly class TableName
{
    public function __construct(
        public string $tableName,
        public ?string $as = null,
    ) {
    }

    public function __toString(): string
    {
        $string = "`{$this->tableName}`";

        if ($this->as !== null)
        {
            $string .= " AS `{$this->as}`";
        }

        return $string;
    }
}
