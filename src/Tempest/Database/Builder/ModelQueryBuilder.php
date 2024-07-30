<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use Tempest\Database\Id;
use Tempest\Database\Model;
use Tempest\Database\Query;
use function Tempest\map;

/**
 * @template TModelClass of Model
 */
final class ModelQueryBuilder
{
    private ModelDefinition $modelDefinition;

    private array $where = [];

    private array $relations = [];

    private array $bindings = [];

    public function __construct(
        /** @var class-string<TModelClass> $modelClass */
        private readonly string $modelClass,
    ) {
        $this->modelDefinition = new ModelDefinition($this->modelClass);
    }

    /** @return TModelClass|null */
    public function first(mixed ...$bindings): ?Model
    {
        $query = $this->build($bindings)->append('LIMIT 1');

        return map($query)->collection()->to($this->modelClass)[0] ?? null;
    }

    /** @return TModelClass|null */
    public function find(Id $id): ?Model
    {
        return $this
            ->whereField('id', $id)
            ->first();
    }

    /** @return TModelClass[] */
    public function all(mixed ...$bindings): array
    {
        return map($this->build($bindings))->collection()->to($this->modelClass);
    }

    /** @return self<TModelClass> */
    public function where(string $where, mixed ...$bindings): self
    {
        $this->where[] = $where;

        $this->bind(...$bindings);

        return $this;
    }

    /** @return self<TModelClass> */
    public function whereField(string $field, mixed $value): self
    {
        $field = $this->modelDefinition->getFieldName($field);

        return $this->where("{$field} = :{$field->fieldName}", ...[$field->fieldName => $value]);
    }

    /** @return self<TModelClass> */
    public function with(string ...$relations): self
    {
        $this->relations = [...$this->relations, ...$relations];

        return $this;
    }

    /** @return self<TModelClass> */
    public function bind(mixed ...$bindings): self
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
    }

    public function toSql(): string
    {
        return $this->build([])->getSql();
    }

    private function build(array $bindings): Query
    {
        $modelDefinition = new ModelDefinition($this->modelClass);

        $relations = $this->getRelations($modelDefinition);

        $fields = $modelDefinition->getFieldNames();

        foreach ($relations as $relation) {
            $fields = [...$fields, ...$relation->getFieldNames()];
        }

        $fields = implode(', ', array_map(
            fn (FieldName $fieldName) => $fieldName->withAlias(),
            $fields,
        ));

        $statements = [];

        $statements[] = sprintf(
            'SELECT %s FROM %s',
            $fields,
            $modelDefinition->getTableName(),
        );

        foreach ($relations as $relation) {
            $statements[] = sprintf(
                'INNER JOIN %s ON %s = %s',
                $relation->getTableName(),
                $relation->getFieldName('id'),
                $relation->getRelationName(),
            );
        }

        if ($this->where !== []) {
            $statements[] = sprintf(
                'WHERE %s',
                implode(' AND ', $this->where),
            );
        }

        return new Query(implode(PHP_EOL, $statements), [...$this->bindings, ...$bindings]);
    }

    /** @return \Tempest\Database\Builder\RelationDefinition[] */
    private function getRelations(ModelDefinition $modelDefinition): array
    {
        $relations = [];

        foreach ($this->relations as $relationName) {
            $relations = [...$relations, ...$modelDefinition->getRelations($relationName)];
        }

        return $relations;
        //        return array_unique([...$relations, ...$modelDefinition->getEagerRelations()]);
    }
}
