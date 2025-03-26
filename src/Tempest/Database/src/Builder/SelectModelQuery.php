<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use Closure;
use Tempest\Database\DatabaseModel;
use Tempest\Database\Id;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Database\QueryStatements\OrderByStatement;
use Tempest\Database\QueryStatements\RawStatement;
use Tempest\Database\QueryStatements\SelectStatement;
use Tempest\Database\QueryStatements\WhereStatement;
use Tempest\Database\Virtual;

use function Tempest\map;
use function Tempest\reflect;

/**
 * @template TModelClass of DatabaseModel
 */
final class SelectModelQuery
{
    private ModelDefinition $modelDefinition;

    private SelectStatement $select;

    private array $relations = [];

    private array $bindings = [];

    public function __construct(
        /** @var class-string<TModelClass> $modelClass */
        private readonly string $modelClass,
    ) {
        $this->modelDefinition = new ModelDefinition($this->modelClass);

        $this->select = new SelectStatement(
            table: $this->modelDefinition->getTableDefinition(),
            columns: $this->modelDefinition
                ->getFieldDefinitions()
                ->filter(fn (FieldDefinition $field) => ! reflect($this->modelClass, $field->name)->hasAttribute(Virtual::class))
                ->map(fn (FieldDefinition $field) => (string) $field->withAlias()),
        );
    }

    /**
     * @return TModelClass|null
     */
    public function first(mixed ...$bindings): mixed
    {
        $query = $this->build($bindings);

        $result = map($query)->collection()->to($this->modelClass);

        if ($result === []) {
            return null;
        }

        return $result[array_key_first($result)];
    }

    /**
     * @return TModelClass|null
     */
    public function get(Id $id): mixed
    {
        return $this->whereField('id', $id)->first();
    }

    /** @return TModelClass[] */
    public function all(mixed ...$bindings): array
    {
        return map($this->build($bindings))->collection()->to($this->modelClass);
    }

    /**
     * @param Closure(TModelClass[] $models): void $closure
     */
    public function chunk(Closure $closure, int $amountPerChunk = 200): void
    {
        $offset = 0;

        do {
            $data = $this->clone()
                ->limit($amountPerChunk)
                ->offset($offset)
                ->all();

            $offset += count($data);

            $closure($data);
        } while ($data !== []);
    }

    /** @return self<TModelClass> */
    public function where(string $where, mixed ...$bindings): self
    {
        $this->select->where[] = new WhereStatement($where);

        $this->bind(...$bindings);

        return $this;
    }

    /** @return self<TModelClass> */
    public function whereField(string $field, mixed $value): self
    {
        $field = $this->modelDefinition->getFieldDefinition($field);

        return $this->where("{$field} = :{$field->name}", ...[$field->name => $value]);
    }

    /** @return self<TModelClass> */
    public function orderBy(string $statement): self
    {
        $this->select->orderBy[] = new OrderByStatement($statement);

        return $this;
    }

    /** @return self<TModelClass> */
    public function limit(int $limit): self
    {
        $this->select->limit = $limit;

        return $this;
    }

    /** @return self<TModelClass> */
    public function offset(int $offset): self
    {
        $this->select->offset = $offset;

        return $this;
    }

    /** @return self<TModelClass> */
    public function with(string ...$relations): self
    {
        $this->relations = [...$this->relations, ...$relations];

        return $this;
    }

    /** @return self<TModelClass> */
    public function raw(string $raw): self
    {
        $this->select->raw[] = new RawStatement($raw);

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
        $resolvedRelations = $this->resolveRelations($this->modelDefinition);

        foreach ($resolvedRelations as $relation) {
            $this->select->columns = $this->select->columns->append(...$relation->getFieldDefinitions()->map(fn (FieldDefinition $field) => (string) $field->withAlias()));
            $this->select->join[] = new JoinStatement($relation->getStatement());
        }

        return new Query($this->select, [...$this->bindings, ...$bindings]);
    }

    /** @return \Tempest\Database\Builder\Relations\Relation[] */
    private function resolveRelations(ModelDefinition $modelDefinition): array
    {
        $relations = $modelDefinition->getEagerRelations();

        foreach ($this->relations as $relationName) {
            foreach ($modelDefinition->getRelations($relationName) as $relation) {
                $relations[$relation->getRelationName()] = $relation;
            }
        }

        return $relations;
    }

    private function clone(): self
    {
        return clone $this;
    }
}
