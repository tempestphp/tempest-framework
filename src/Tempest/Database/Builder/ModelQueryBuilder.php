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

        /*
         * 0. Build relation array
         * 1. Selecting fields
         *      - Own fields
         *      - All relation fields
         * 2. Joining relations
         *      - BelongsTo
         *      - HasMany
         *      - …
         * 3. Applying where,order by, limit, etc.
         * 4. Create query
         */


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
            $statements[] = $relation->getStatement();
        }

        if ($this->where !== []) {
            $statements[] = sprintf(
                'WHERE %s',
                implode(' AND ', $this->where),
            );
        }
ld(implode(PHP_EOL, $statements));
        return new Query(implode(PHP_EOL, $statements), [...$this->bindings, ...$bindings]);
    }

    /** @return \Tempest\Database\Builder\Relations\Relation[] */
    private function getRelations(ModelDefinition $modelDefinition): array
    {
        $relations = $modelDefinition->getEagerRelations();

        foreach ($this->relations as $relationName) {
            foreach ($modelDefinition->getRelations($relationName) as $relation) {
                $relations[$relation->getRelationName()] = $relation;
            }
        }

        return $relations;
    }
}
