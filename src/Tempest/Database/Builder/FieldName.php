<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use BackedEnum;
use Tempest\Mapper\CastWith;
use Tempest\Support\Reflection\ClassReflector;

final class FieldName
{
    public function __construct(
        public readonly TableName $tableName,
        public readonly string $fieldName,
        public ?string $as = null,
    ) {
    }

    /** @return \Tempest\Database\Builder\FieldName[] */
    public static function make(ClassReflector $class, ?TableName $tableName = null): array
    {
        $fieldNames = [];
        $tableName ??= $class->callStatic('table');

        foreach ($class->getPublicProperties() as $property) {
            $type = $property->getType();

            if ($type->matches(BackedEnum::class)) {
                $fieldNames[] = new FieldName($tableName, $property->getName());

                continue;
            }

            if (! $type->isBuiltin()) {
                $castWith = $property->getAttribute(CastWith::class)
                    ?? $type->asClass()->getAttribute(CastWith::class);

                if ($castWith) {
                    $fieldNames[] = new FieldName($tableName, $property->getName());
                }

                continue;
            }

            if ($type->isIterable()) {
                continue;
            }

            $fieldNames[] = new FieldName($tableName, $property->getName());
        }

        return $fieldNames;
    }

    public function as(string $as): self
    {
        $this->as = $as;

        return $this;
    }

    public function withAlias(): self
    {
        $tableName = $this->tableName->as ?? $this->tableName->tableName;

        return $this->as($tableName . '.' . $this->fieldName);
    }

    public function __toString(): string
    {
        $tableName = $this->tableName->as ?? $this->tableName->tableName;

        $string = "`{$tableName}`.`{$this->fieldName}`";

        if ($this->as) {
            $string .= " AS `{$this->as}`";
        }

        return $string;
    }
}
