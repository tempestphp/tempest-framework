<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\QueryBuilders;

use Closure;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\Id;
use Tempest\Database\Mappers\SelectModelMapper;
use Tempest\Database\OnDatabase;
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

use function Tempest\Database\model;
use function Tempest\map;

/**
 * @template TModelClass of object
 * @implements \Tempest\Database\Builder\QueryBuilders\BuildsQuery<TModelClass>
 * @uses \Tempest\Database\Builder\QueryBuilders\HasWhereQueryBuilderMethods<TModelClass>
 */
final class SelectQueryBuilder implements BuildsQuery
{
    use HasConditions, OnDatabase, HasWhereQueryBuilderMethods, TapsQueryBuilder;

    private ModelInspector $model;

    private SelectStatement $select;

    private array $joins = [];

    private array $relations = [];

    private array $bindings = [];

    public function __construct(
        /** @var class-string<TModelClass>|string|TModelClass $model */
        string|object $model,
        ?ImmutableArray $fields = null,
    ) {
        $this->model = model($model);

        $this->select = new SelectStatement(
            table: $this->model->getTableDefinition(),
            fields: $fields ?? $this->model
                ->getSelectFields()
                ->map(fn (string $fieldName) => new FieldStatement("{$this->model->getTableName()}.{$fieldName}")->withAlias()),
        );
    }

    /** @return TModelClass|null */
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

    /** @return PaginatedData<TModelClass> */
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

    /** @return TModelClass|null */
    public function get(Id $id): mixed
    {
        return $this->whereField('id', $id)->first();
    }

    /** @return TModelClass[] */
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
    public function orderBy(string $statement): self
    {
        $this->select->orderBy[] = new OrderByStatement($statement);

        return $this;
    }

    /** @return self<TModelClass> */
    public function groupBy(string $statement): self
    {
        $this->select->groupBy[] = new GroupByStatement($statement);

        return $this;
    }

    /** @return self<TModelClass> */
    public function having(string $statement, mixed ...$bindings): self
    {
        $this->select->having[] = new HavingStatement($statement);

        $this->bind(...$bindings);

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
        return $this->build()->toSql();
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

    /** @return \Tempest\Database\Relation[] */
    private function getIncludedRelations(): array
    {
        $definition = model($this->model->getName());

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

    private function clone(): self
    {
        return clone $this;
    }
}
