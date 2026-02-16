<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Exceptions\CannotCountDistinctWithoutSpecifyingAColumn;
use Tempest\Database\OnDatabase;
use Tempest\Database\Query;
use Tempest\Database\QueryStatements\CountStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Conditions\HasConditions;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Database\inspect;
use function Tempest\Support\arr;

/**
 * @template TModel of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModel>
 * @implements \Tempest\Database\Builder\QueryBuilders\SupportsWhereStatements<TModel>
 */
final class CountQueryBuilder implements BuildsQuery, SupportsWhereStatements
{
    use HasConditions;
    use OnDatabase;
    /** @use HasWhereQueryBuilderMethods<TModel> */
    use HasWhereQueryBuilderMethods;
    use TransformsQueryBuilder;

    private CountStatement $count;

    public ModelInspector $model;

    public array $bindings = [];

    /** @var array<JoinStatement|string> */
    public array $joins = [];

    public ImmutableArray $wheres {
        get => $this->count->where;
    }

    /**
     * @param class-string<TModel>|string|TModel $model
     */
    public function __construct(string|object $model, ?string $column = null)
    {
        $this->model = inspect($model);

        $this->count = new CountStatement(
            table: $this->model->getTableDefinition(),
            column: $column,
        );
    }

    /**
     * Creates an instance from another query builder, inheriting conditions and bindings.
     *
     * @template TSourceModel of object
     * @param (BuildsQuery<TSourceModel>&SupportsWhereStatements<TSourceModel>) $source
     * @param string|null $column
     * @return CountQueryBuilder<TSourceModel>
     */
    public static function fromQueryBuilder(BuildsQuery&SupportsWhereStatements $source, ?string $column = null): CountQueryBuilder
    {
        $builder = new self($source->model->getName(), $column);
        $builder->bind(...$source->bindings);

        foreach ($source->wheres as $where) {
            $builder->appendWhere($where);
        }

        if ($source instanceof SupportsJoins) {
            $builder->joins = $source->joins;
        }

        if ($source instanceof SupportsRelations) {
            foreach ($source->getResolvedRelations() as $relation) {
                $builder->joins[] = $relation->getJoinStatement();
            }
        }

        /** @var CountQueryBuilder<TSourceModel> $builder */
        return $builder;
    }

    /**
     * Executes the count query and returns the number of matching records.
     */
    public function execute(mixed ...$bindings): int
    {
        return $this->build()->fetchFirst(...$bindings)[$this->count->getKey()];
    }

    /**
     * Modifies the count query to only count distinct values in the specified column.
     *
     * @return self<TModel>
     */
    public function distinct(): self
    {
        if ($this->count->column === null || $this->count->column === '*') {
            throw new CannotCountDistinctWithoutSpecifyingAColumn();
        }

        $this->count->distinct = true;

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
        if ($this->joins !== []) {
            $this->count->joins = arr($this->joins)
                ->map(fn (JoinStatement|string $join) => $join instanceof JoinStatement ? $join : new JoinStatement($join));
        }

        return new Query($this->count, [...$this->bindings, ...$bindings])->onDatabase($this->onDatabase);
    }
}
