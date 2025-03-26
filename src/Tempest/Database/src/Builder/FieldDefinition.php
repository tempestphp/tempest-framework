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

    /** @return ImmutableArray<\Tempest\Database\Builder\FieldDefinition> */
    public static function make(ClassReflector $class, ?TableDefinition $tableDefinition = null): ImmutableArray
    {
        $casterFactory = get(CasterFactory::class);
        $fieldDefinitions = [];
        $tableDefinition ??= new ModelDefinition($class->getName())->getTableDefinition();

        foreach ($class->getPublicProperties() as $property) {
            // Don't include the field if it's a 1:1 or n:1 relation
            if ($property->getType()->isRelation()) {
                continue;
            }

            // Don't include the field if it's a 1:n relation
            if ($property->getIterableType()?->isRelation()) {
                continue;
            }

            $caster = $casterFactory->forProperty($property);

            if ($caster !== null) {
                $fieldDefinitions[] = new FieldDefinition($tableDefinition, $property->getName());

                continue;
            }

            if (! $property->getType()->isBuiltIn()) {
                continue;
            }

            $fieldDefinitions[] = new FieldDefinition($tableDefinition, $property->getName());
        }

        return new ImmutableArray($fieldDefinitions);
    }

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
