<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use Stringable;
use Tempest\Support\Reflection\ClassReflector;

final readonly class TableName implements Stringable
{
    public function __construct(
        public string $tableName,
        public ?string $as = null,
    ) {
    }

    public static function for(ClassReflector $reflector, ?string $as = null): self
    {
        return $reflector->callStatic('table')->as($as);
    }

    public function as(?string $as = null): self
    {
        if ($as === null) {
            return $this;
        }

        return new self($this->tableName, $as);
    }

    public function __toString(): string
    {
        $string = "`{$this->tableName}`";

        if ($this->as !== null) {
            $string .= " AS `{$this->as}`";
        }

        return $string;
    }
}
