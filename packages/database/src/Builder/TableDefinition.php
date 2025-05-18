<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use Stringable;
use Tempest\Reflection\ClassReflector;

// TODO: remove
final readonly class TableDefinition implements Stringable
{
    public function __construct(
        public string $name,
        public ?string $as = null,
    ) {}

    public static function for(ClassReflector $reflector, ?string $as = null): self
    {
        return new ModelDefinition($reflector->getName())
            ->getTableDefinition()
            ->as($as);
    }

    public function as(?string $as = null): self
    {
        if ($as === null) {
            return $this;
        }

        return new self($this->name, $as);
    }

    public function __toString(): string
    {
        $string = "`{$this->name}`";

        if ($this->as !== null) {
            $string .= " AS `{$this->as}`";
        }

        return $string;
    }
}
