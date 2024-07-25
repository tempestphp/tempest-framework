<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use BackedEnum;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use function Tempest\attribute;
use Tempest\Database\Model;
use Tempest\Database\Query;
use function Tempest\map;
use Tempest\Mapper\CastWith;
use function Tempest\type;

/**
 * @template TModelClass of Model
 */
final class ModelQueryBuilder
{
    private array $where = [];

    private array $relations = [];

    public function __construct(
        /** @var class-string<TModelClass> $modelClass */
        private readonly string $modelClass,
    ) {
    }

    /** @return TModelClass */
    public function first(array $bindings = []): Model
    {
        $query = $this->build($bindings)->append('LIMIT 1');

        return map($query)->collection()->to($this->modelClass)[0];
    }

    /** @return TModelClass[] */
    public function all(array $bindings = []): array
    {
        return map($this->build($bindings))->collection()->to($this->modelClass);
    }

    public function where(string ...$where): self
    {
        $this->where = [...$this->where, ...$where];

        return $this;
    }

    public function with(string ...$relations): self
    {
        $this->relations = [...$this->relations, ...$relations];

        return $this;
    }

    private function build(array $bindings): Query
    {
        $fields = $this->getFieldNames($this->modelClass);

        /** @var class-string<\Tempest\Database\Model> $relation */
        foreach ($this->relations as $relation) {
            $fields = [...$fields, ...$this->getFieldNames($relation)];
        }

        $fields = implode(', ', array_map(
            fn (FieldName $fieldName) => $fieldName->asDefault(),
            $fields,
        ));

        $statements = [];
        $statements[] = "SELECT {$fields} FROM " . $this->getTableName($this->modelClass);

        foreach ($this->relations as $relation) {
            $statements[] = 'INNER JOIN ' . $this->getTableName($relation) . ' ON ' . $this->getFieldName($relation, 'id') . ' = ' . $this->getRelationFieldName($this->modelClass, $relation);
        }

        if ($this->where !== []) {
            $statements[] = 'WHERE ';
            $statements[] = implode(' AND ', $this->where);
        }

        return new Query(implode(PHP_EOL, $statements), $bindings);
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function getTableName(string $modelClass): TableName
    {
        return $modelClass::table();
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function getFieldName(string $modelClass, string $name): FieldName
    {
        return $modelClass::field($name);
    }

    /**
     * @param class-string<Model> $modelClass
     */
    private function getRelationFieldName(string $modelClass, string $relationName): FieldName
    {
        $field = lcfirst(pathinfo(str_replace('\\', '/', $relationName), PATHINFO_FILENAME)) . '_id';

        return $this->getFieldName($modelClass, $field);
    }

    private function getFieldNames(string $modelClass): array
    {
        $fieldNames = [];

        foreach ((new ReflectionClass($modelClass))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (is_a(type($property), BackedEnum::class, true)) {
                $fieldNames[] = $this->getFieldName($modelClass, $property->getName());

                continue;
            }

            $type = $property->getType();

            if (! $type instanceof ReflectionNamedType) {
                continue;
            }

            if (! $type->isBuiltin()) {
                $castWith = attribute(CastWith::class)
                    ->in($property)
                    ->first();

                if (! $castWith) {
                    $castWith = attribute(CastWith::class)
                        ->in($type->getName())
                        ->first();
                }

                if ($castWith) {
                    $fieldNames[] = $this->getFieldName($modelClass, $property->getName());
                }

                continue;
            }

            if ($type->getName() === 'array') {
                continue;
            }

            $fieldNames[] = $this->getFieldName($modelClass, $property->getName());
        }

        return $fieldNames;
    }
}
