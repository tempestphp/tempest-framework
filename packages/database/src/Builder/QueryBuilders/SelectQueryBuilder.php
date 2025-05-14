<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\FieldDefinition;
use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Id;
use Tempest\Database\Mappers\DatabaseModelMapper;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Database\QueryStatements\OrderByStatement;
use Tempest\Database\QueryStatements\RawStatement;
use Tempest\Database\QueryStatements\SelectStatement;
use Tempest\Database\QueryStatements\WhereStatement;
use Tempest\Database\Virtual;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Conditions\HasConditions;

use function Tempest\map;
use function Tempest\reflect;
use function Tempest\Support\arr;

/**
 * @template TModelClass of object
 */
final class SelectQueryBuilder implements BuildsQuery
{
    use HasConditions;

    /** @var class-string<TModelClass> $modelClass */
    private readonly string $modelClass;

    private ?ModelDefinition $modelDefinition;

    private SelectStatement $select;

    private array $joins = [];

    private array $relations = [];

    private array $bindings = [];

    public function __construct(string|object $model, ?ImmutableArray $columns = null)
    {
        $this->modelDefinition = ModelDefinition::tryFrom($model);
        $this->modelClass = is_object($model) ? $model::class : $model;

        $this->select = new SelectStatement(
            table: $this->resolveTable($model),
            columns: $columns ?? $this->resolveColumns(),
        );
    }

    /**
     * @return TModelClass|null
     */
    public function first(mixed ...$bindings): mixed
    {
        $query = $this->build(...$bindings);

        if (! $this->modelDefinition) {
            return $query->fetchFirst();
        }

        $result = map($query)
            ->collection()
            ->with(DatabaseModelMapper::class)
            ->to($this->modelClass);

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
        $query = $this->build(...$bindings);

        if (! $this->modelDefinition) {
            return $query->fetch();
        }

        return map($query)->collection()->to($this->modelClass);
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

    public function andWhere(string $where, mixed ...$bindings): self
    {
        return $this->where("AND {$where}", ...$bindings);
    }

    public function orWhere(string $where, mixed ...$bindings): self
    {
        return $this->where("OR {$where}", ...$bindings);
    }

    /** @return self<TModelClass> */
    public function whereField(string $field, mixed $value): self
    {
        if ($this->modelDefinition) {
            $field = $this->modelDefinition->getFieldDefinition($field);
        } else {
            $field = new FieldDefinition(
                $this->resolveTable($this->modelClass),
                $field,
            );
        }

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
    public function join(string ...$joins): self
    {
        $this->joins = [...$this->joins, ...$joins];

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
        return $this->build()->getSql();
    }

    public function build(mixed ...$bindings): Query
    {
        $resolvedRelations = $this->resolveRelations();

        foreach ($this->joins as $join) {
            $this->select->join[] = new JoinStatement($join);
        }

        foreach ($resolvedRelations as $relation) {
            $this->select->columns = $this->select->columns->append(...$relation->getFieldDefinitions()->map(fn (FieldDefinition $field) => (string) $field->withAlias()));
            $this->select->join[] = new JoinStatement($relation->getStatement());
        }

        return new Query($this->select, [...$this->bindings, ...$bindings]);
    }

    private function clone(): self
    {
        return clone $this;
    }

    private function resolveTable(string|object $model): TableDefinition
    {
        if ($this->modelDefinition === null) {
            return new TableDefinition($model);
        }

        return $this->modelDefinition->getTableDefinition();
    }

    private function resolveColumns(): ImmutableArray
    {
        if ($this->modelDefinition === null) {
            return arr();
        }

        return $this->modelDefinition
            ->getFieldDefinitions()
            ->filter(fn (FieldDefinition $field) => ! reflect($this->modelClass, $field->name)->hasAttribute(Virtual::class))
            ->map(fn (FieldDefinition $field) => (string) $field->withAlias());
    }

    private function resolveRelations(): ImmutableArray
    {
        if ($this->modelDefinition === null) {
            return arr();
        }

        $relations = $this->modelDefinition->getEagerRelations();

        foreach ($this->relations as $relationName) {
            foreach ($this->modelDefinition->getRelations($relationName) as $relation) {
                $relations[$relation->getRelationName()] = $relation;
            }
        }

        return arr($relations);
    }
}
