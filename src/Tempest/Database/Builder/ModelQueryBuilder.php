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

    private array $orderBy = [];

    private ?int $limit = null;

    private array $raw = [];

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
        $query = $this->build($bindings);

        $result = map($query)->collection()->to($this->modelClass);

        if ($result === []) {
            return null;
        }

        return $result[array_key_first($result)];
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

    public function orderBy(string $statement): self
    {
        $this->orderBy[] = $statement;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function raw(string $raw): self
    {
        $this->raw[] = $raw;

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
            $statements[] = $relation->getStatement();
        }

        if ($this->where !== []) {
            $statements[] = sprintf(
                'WHERE %s',
                implode(' AND ', $this->where),
            );
        }

        if ($this->orderBy !== []) {
            $statements[] = sprintf(
                'ORDER BY %s',
                implode(', ', $this->orderBy),
            );
        }

        if ($this->limit) {
            $statements[] = sprintf('LIMIT %s', $this->limit);
        }

        if ($this->raw !== []) {
            $statements[] = implode(', ', $this->raw);
        }

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
