<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\Mappers\SelectModelMapper;
use Tempest\Database\OnDatabase;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\FieldStatement;
use Tempest\Database\QueryStatements\GroupByStatement;
use Tempest\Database\QueryStatements\HasWhereStatements;
use Tempest\Database\QueryStatements\HavingStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Database\QueryStatements\OrderByStatement;
use Tempest\Database\QueryStatements\RawStatement;
use Tempest\Database\QueryStatements\SelectStatement;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Paginator\PaginatedData;
use Tempest\Support\Paginator\Paginator;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Database\inspect;
use function Tempest\map;

/**
 * @template T of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<T>
 * @uses \Tempest\Database\Builder\QueryBuilders\HasWhereQueryBuilderMethods<T>
 */
final class SelectQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase, HasWhereQueryBuilderMethods;

    private ModelInspector $model;

    private SelectStatement $select;

    private array $joins = [];

    private array $relations = [];

    private array $bindings = [];

    /**
     * @param class-string<T>|string|T $model
     */
    public function __construct(string|object $model, ?ImmutableArray $fields = null)
    {
        $this->model = inspect($model);

        $this->select = new SelectStatement(
            table: $this->model->getTableDefinition(),
            fields: $fields ?? $this->model
                ->getSelectFields()
                ->map(fn (string $fieldName) => new FieldStatement("{$this->model->getTableName()}.{$fieldName}")->withAlias()),
        );
    }

    /**
     * Returns the first record matching the query.
     *
     * @return T|null
     */
    public function first(mixed ...$bindings): mixed
    {
        $query = $this->build(...$bindings);

        if (! $this->model->isObjectModel()) {
            return $query->fetchFirst();
        }

        $result = map($query->fetch())
            ->with(SelectModelMapper::class)
            ->to($this->model->getName());

        if ($result === []) {
            return null;
        }

        return $result[array_key_first($result)];
    }

    /**
     * Returnd length-aware paginated data for the current query.
     *
     * @return PaginatedData<T>
     */
    public function paginate(int $itemsPerPage = 20, int $currentPage = 1, int $maxLinks = 10): PaginatedData
    {
        $total = new CountQueryBuilder($this->model->model)->execute();

        $paginator = new Paginator(
            totalItems: $total,
            itemsPerPage: $itemsPerPage,
            currentPage: $currentPage,
            maxLinks: $maxLinks,
        );

        return $paginator->paginateWith(
            callback: fn (int $limit, int $offset) => $this->limit($limit)->offset($offset)->all(),
        );
    }

    /**
     * Returns the first record matching the given primary key.
     *
     * @return T|null
     */
    public function get(PrimaryKey $id): mixed
    {
        if (! $this->model->hasPrimaryKey()) {
            throw ModelDidNotHavePrimaryColumn::neededForMethod($this->model->getName(), 'get');
        }

        return $this->where($this->model->getPrimaryKey(), $id)->first();
    }

    /**
     * Returns all records matching the query.
     *
     * @return T[]
     */
    public function all(mixed ...$bindings): array
    {
        $query = $this->build(...$bindings);

        if (! $this->model->isObjectModel()) {
            return $query->fetch();
        }

        return map($query->fetch())
            ->with(SelectModelMapper::class)
            ->to($this->model->getName());
    }

    /**
     * Performs multiple queries in chunks, passing each chunk to the provided closure.
     *
     * @param Closure(T[]): void $closure
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

    /**
     * Orders the results of the query by the given raw SQL statement.
     *
     * @return self<T>
     */
    public function orderBy(string $statement): self
    {
        $this->select->orderBy[] = new OrderByStatement($statement);

        return $this;
    }

    /**
     * Groups the results of the query by the given raw SQL statement.
     *
     * @return self<T>
     */
    public function groupBy(string $statement): self
    {
        $this->select->groupBy[] = new GroupByStatement($statement);

        return $this;
    }

    /**
     * Adds a `HAVING` clause to the query with the given raw SQL statement.
     *
     * @return self<T>
     */
    public function having(string $statement, mixed ...$bindings): self
    {
        $this->select->having[] = new HavingStatement($statement);

        $this->bind(...$bindings);

        return $this;
    }

    /**
     * Limits the number of results returned by the query by the specified amount.
     *
     * @return self<T>
     */
    public function limit(int $limit): self
    {
        $this->select->limit = $limit;

        return $this;
    }

    /**
     * Sets the offset for the query, allowing you to skip a number of results.
     *
     * @return self<T>
     */
    public function offset(int $offset): self
    {
        $this->select->offset = $offset;

        return $this;
    }

    /**
     * Joins the specified tables to the query using raw SQL statements, allowing for complex queries across multiple tables.
     *
     * @return self<T>
     */
    public function join(string ...$joins): self
    {
        $this->joins = [...$this->joins, ...$joins];

        return $this;
    }

    /**
     * Includes the specified relationships in the query, allowing for eager loading.
     *
     * @return self<T>
     */
    public function with(string ...$relations): self
    {
        $this->relations = [...$this->relations, ...$relations];

        return $this;
    }

    /**
     * Adds a raw SQL statement to the query.
     *
     * @return self<T>
     */
    public function raw(string $raw): self
    {
        $this->select->raw[] = new RawStatement($raw);

        return $this;
    }

    /**
     * Binds the provided values to the query, allowing for parameterized queries.
     *
     * @return self<T>
     */
    public function bind(mixed ...$bindings): self
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
    }

    /**
     * Returns the SQL statement without the bindings.
     */
    public function toSql(): ImmutableString
    {
        return $this->build()->toSql();
    }

    /**
     * Returns the SQL statement with bindings. This method may generate syntax errors, it is not recommended to use it other than for debugging.
     */
    public function toRawSql(): ImmutableString
    {
        return $this->build()->toRawSql();
    }

    public function build(mixed ...$bindings): Query
    {
        foreach ($this->joins as $join) {
            $this->select->join[] = new JoinStatement($join);
        }

        foreach ($this->getIncludedRelations() as $relation) {
            $this->select->fields = $this->select->fields->append(
                ...$relation->getSelectFields(),
            );

            $this->select->join[] = $relation->getJoinStatement();
        }

        return new Query($this->select, [...$this->bindings, ...$bindings])->onDatabase($this->onDatabase);
    }

    private function clone(): self
    {
        return clone $this;
    }

    /** @return \Tempest\Database\Relation[] */
    private function getIncludedRelations(): array
    {
        $definition = inspect($this->model->getName());

        if (! $definition->isObjectModel()) {
            return [];
        }

        $relations = $definition->resolveEagerRelations();

        foreach ($this->relations as $relationString) {
            $resolvedRelations = $definition->resolveRelations($relationString);

            if ($resolvedRelations === []) {
                continue;
            }

            $relations = [...$relations, ...$resolvedRelations];
        }

        return $relations;
    }

    private function getStatementForWhere(): HasWhereStatements
    {
        return $this->select;
    }

    private function getModel(): ModelInspector
    {
        return $this->model;
    }
}
