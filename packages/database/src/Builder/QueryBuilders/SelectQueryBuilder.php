<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Database;
use Tempest\Database\DatabaseContext;
use Tempest\Database\Direction;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\Mappers\SelectModelMapper;
use Tempest\Database\OnDatabase;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\FieldStatement;
use Tempest\Database\QueryStatements\GroupByStatement;
use Tempest\Database\QueryStatements\HavingStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Database\QueryStatements\OrderByStatement;
use Tempest\Database\QueryStatements\RawStatement;
use Tempest\Database\QueryStatements\SelectStatement;
use Tempest\Database\Relation;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Paginator\PaginatedData;
use Tempest\Support\Paginator\Paginator;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Container\get;
use function Tempest\Database\inspect;
use function Tempest\Mapper\map;

/**
 * @template TModel of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModel>
 * @implements \Tempest\Database\Builder\QueryBuilders\SupportsWhereStatements<TModel>
 * @implements \Tempest\Database\Builder\QueryBuilders\SupportsJoins<TModel>
 * @implements \Tempest\Database\Builder\QueryBuilders\SupportsRelations<TModel>
 * @use \Tempest\Database\Builder\QueryBuilders\HasWhereQueryBuilderMethods<TModel>
 */
final class SelectQueryBuilder implements BuildsQuery, SupportsWhereStatements, SupportsJoins, SupportsRelations
{
    use HasConditions, OnDatabase, HasWhereQueryBuilderMethods, TransformsQueryBuilder;

    public ModelInspector $model;

    private SelectStatement $select;

    public array $joins = [];

    public array $relations = [];

    public array $bindings = [];

    private Database $database {
        get => get(Database::class, $this->onDatabase);
    }

    private DatabaseContext $context {
        get => new DatabaseContext(dialect: $this->database->dialect);
    }

    public ImmutableArray $wheres {
        get => $this->select->where;
        set => $this->select->where;
    }

    /**
     * @param class-string<TModel>|string|TModel $model
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
     * @return TModel|null
     */
    public function first(mixed ...$bindings): mixed
    {
        $query = $this->build(...$bindings);

        if (! $this->model->isObjectModel()) {
            return $query->fetchFirst();
        }

        $result = map($query->fetch())
            ->with(SelectModelMapper::class)
            ->in($this->context)
            ->to($this->model->getName());

        if ($result === []) {
            return null;
        }

        return $result[array_key_first($result)];
    }

    /**
     * Returns length-aware paginated data for the current query.
     *
     * @return PaginatedData<TModel>
     */
    public function paginate(int $itemsPerPage = 20, int $currentPage = 1, int $maxLinks = 10): PaginatedData
    {
        $total = CountQueryBuilder::fromQueryBuilder($this)->execute();

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
     * @return TModel|null
     */
    public function get(PrimaryKey $id): mixed
    {
        if (! $this->model->hasPrimaryKey()) {
            throw ModelDidNotHavePrimaryColumn::neededForMethod($this->model->getName(), 'get');
        }

        return $this->whereField($this->model->getPrimaryKey(), $id)->first();
    }

    /**
     * Creates an instance from another query builder, inheriting conditions and bindings.
     *
     * @template TSourceModel of object
     * @param (BuildsQuery<TSourceModel>&SupportsWhereStatements<TSourceModel>) $source
     * @return SelectQueryBuilder<TSourceModel>
     */
    public static function fromQueryBuilder(BuildsQuery&SupportsWhereStatements $source, mixed ...$fields): SelectQueryBuilder
    {
        $builder = new self($source->model->model, ...$fields);
        $builder->bind(...$source->bindings);

        foreach ($source->wheres as $where) {
            $builder->wheres[] = $where;
        }

        if ($source instanceof SupportsJoins) {
            $builder->joins = $source->joins;
        }

        if ($source instanceof SupportsRelations) {
            foreach ($source->getResolvedRelations() as $relation) {
                $builder->joins[] = $relation->getJoinStatement();
            }
        }

        return $builder;
    }

    /**
     * Returns all records matching the query.
     *
     * @return TModel[]
     */
    public function all(mixed ...$bindings): array
    {
        $query = $this->build(...$bindings);

        if (! $this->model->isObjectModel()) {
            return $query->fetch();
        }

        return map($query->fetch())
            ->with(SelectModelMapper::class)
            ->in($this->context)
            ->to($this->model->getName());
    }

    /**
     * Performs multiple queries in chunks, passing each chunk to the provided closure.
     *
     * @param Closure(TModel[]): void $closure
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
     * Orders the results of the query by the given field name and direction.
     *
     * @return self<TModel>
     */
    public function orderBy(string $field, Direction $direction = Direction::ASC): self
    {
        if (str_contains($field, ' ')) {
            return $this->orderByRaw($field);
        }

        $this->select->orderBy[] = new OrderByStatement("`{$field}` {$direction->value}");

        return $this;
    }

    /**
     * Orders the results of the query by the given raw SQL statement.
     *
     * @return self<TModel>
     */
    public function orderByRaw(string $statement): self
    {
        $this->select->orderBy[] = new OrderByStatement($statement);

        return $this;
    }

    /**
     * Groups the results of the query by the given raw SQL statement.
     *
     * @return self<TModel>
     */
    public function groupBy(string $statement): self
    {
        $this->select->groupBy[] = new GroupByStatement($statement);

        return $this;
    }

    /**
     * Adds a `HAVING` clause to the query with the given raw SQL statement.
     *
     * @return self<TModel>
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
     * @return self<TModel>
     */
    public function limit(int $limit): self
    {
        $this->select->limit = $limit;

        return $this;
    }

    /**
     * Sets the offset for the query, allowing you to skip a number of results.
     *
     * @return self<TModel>
     */
    public function offset(int $offset): self
    {
        $this->select->offset = $offset;

        return $this;
    }

    /**
     * Joins the specified tables to the query using raw SQL statements, allowing for complex queries across multiple tables.
     *
     * @return self<TModel>
     */
    public function join(string ...$joins): self
    {
        $this->joins = [...$this->joins, ...$joins];

        return $this;
    }

    /**
     * Includes the specified relationships in the query, allowing for eager loading.
     *
     * @return self<TModel>
     */
    public function with(string ...$relations): self
    {
        $this->relations = [...$this->relations, ...$relations];

        return $this;
    }

    /**
     * Includes additional fields in the query. Useful for including hidden fields.
     *
     * @return self<TModel>
     */
    public function include(string ...$fields): self
    {
        $tableName = $this->model->getTableName();
        $existingFields = $this->model->getSelectFields()->toArray();

        foreach ($fields as $field) {
            if (in_array($field, $existingFields, true)) {
                continue;
            }

            $existingFields[] = $field;
            $this->select->fields[] = new FieldStatement("{$tableName}.{$field}")->withAlias();
        }

        return $this;
    }

    /**
     * Adds a raw SQL statement to the query.
     *
     * @return self<TModel>
     */
    public function raw(string $raw): self
    {
        $this->select->raw[] = new RawStatement($raw);

        return $this;
    }

    /**
     * Binds the provided values to the query, allowing for parameterized queries.
     *
     * @return self<TModel>
     */
    public function bind(mixed ...$bindings): self
    {
        $this->bindings = [...$this->bindings, ...$bindings];

        return $this;
    }

    /**
     * Compile the query to a SQL statement without the bindings.
     */
    public function compile(): ImmutableString
    {
        return $this->build()->compile();
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
        $select = clone $this->select;

        foreach ($this->joins as $join) {
            $select = $select->withJoin(new JoinStatement($join));
        }

        foreach ($this->getResolvedRelations() as $relation) {
            $select = $select
                ->withFields($relation->getSelectFields())
                ->withJoin($relation->getJoinStatement());
        }

        return new Query($select, [...$this->bindings, ...$bindings])->onDatabase($this->onDatabase);
    }

    private function clone(): self
    {
        return clone $this;
    }

    /**
     * Gets all resolved relations with their join statements.
     *
     * @return Relation[]
     */
    public function getResolvedRelations(): array
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

    public function setWhereStatements(QueryStatement ...$statements): self
    {
        $this->select->where = new ImmutableArray($statements);

        return $this;
    }

    public function getWhereStatements(): ImmutableArray
    {
        return $this->select->where;
    }
}
