<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use Stringable;
use Tempest\Mapper\CasterFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\get;

final class FieldDefinition implements Stringable
{
    public function __construct(
        public readonly TableDefinition $tableDefinition,
        public readonly string $name,
        public ?string $as = null,
    ) {}

    public function as(string $as): self
    {
        $this->as = $as;

        return $this;
    }

    public function withAlias(): self
    {
        $name = $this->tableDefinition->as ?? $this->tableDefinition->name;

        return $this->as($name . '.' . $this->name);
    }

    public function __toString(): string
    {
        $tableName = $this->tableDefinition->as ?? $this->tableDefinition->name;

        $string = "`{$tableName}`.`{$this->name}`";

        if ($this->as) {
            $string .= " AS `{$this->as}`";
        }

        return $string;
    }
}
