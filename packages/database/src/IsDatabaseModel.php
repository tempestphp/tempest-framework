<?php

declare(strict_types=1);

namespace Tempest\Database;

use ArrayAccess;
use Closure;
use Countable;
use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\InsertQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Builder\QueryBuilders\QueryScope;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\Exceptions\PrimaryKeyWasNotInitialized;
use Tempest\Database\Exceptions\PropertyWasNotARelation;
use Tempest\Database\Exceptions\RelationWasMissing;
use Tempest\Database\Exceptions\ValueWasMissing;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Reflection\PropertyReflector;
use Tempest\Router\IsBindingValue;
use Tempest\Support\Paginator\PaginatedData;
use Tempest\Support\Paginator\SimplePaginatedData;
use Tempest\Validation\SkipValidation;
use UnitEnum;

use function Tempest\Support\arr;
use function Tempest\Support\str;

trait IsDatabaseModel
{
    #[IsBindingValue, SkipValidation]
    public PrimaryKey $id;

    #[SkipValidation, Virtual]
    private string|UnitEnum|null $onDatabase = null;

    /**
     * Returns a query builder targeting the specified database connection.
     *
     * @return QueryBuilder<static>
     */
    public static function on(string|UnitEnum|null $databaseTag): QueryBuilder
    {
        return static::queryBuilder()->onDatabase(databaseTag: $databaseTag);
    }

    /**
     * Targets a specific database connection for this model instance.
     */
    public function onDatabase(string|UnitEnum|null $databaseTag): static
    {
        $clone = clone $this;

        $clone->onDatabase = $databaseTag;

        return $clone;
    }

    /** @return QueryBuilder<static> */
    protected static function queryBuilder(): QueryBuilder
    {
        return query(static::class);
    }

    /**
     * Returns a builder for selecting records using this model's table.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function select(): SelectQueryBuilder
    {
        return static::queryBuilder()->select();
    }

    /**
     * Returns a builder for inserting records using this model's table.
     *
     * @return InsertQueryBuilder<static>
     */
    public static function insert(): InsertQueryBuilder
    {
        return static::queryBuilder()->insert();
    }

    /**
     * Returns a builder for counting records using this model's table.
     *
     * @return CountQueryBuilder<static>
     */
    public static function count(): CountQueryBuilder
    {
        return static::queryBuilder()->count();
    }

    /**
     * Executes an aggregate query and returns the sum of the given column.
     */
    public static function sum(string $column): int|float
    {
        return static::queryBuilder()->sum(column: $column);
    }

    /**
     * Executes an aggregate query and returns the average of the given column.
     */
    public static function avg(string $column): float
    {
        return static::queryBuilder()->avg(column: $column);
    }

    /**
     * Executes an aggregate query and returns the maximum value of the given column.
     */
    public static function max(string $column): mixed
    {
        return static::queryBuilder()->max(column: $column);
    }

    /**
     * Executes an aggregate query and returns the minimum value of the given column.
     */
    public static function min(string $column): mixed
    {
        return static::queryBuilder()->min(column: $column);
    }

    /**
     * Creates a new instance of this model without persisting it to the database.
     */
    public static function new(mixed ...$params): static
    {
        // @phpstan-ignore-next-line
        return static::queryBuilder()->new(...$params);
    }

    /**
     * Finds a model instance by its ID.
     */
    public static function findById(string|int|PrimaryKey $id): ?static
    {
        return static::get($id);
    }

    /**
     * Finds a model instance by its ID. Use through {@see \Tempest\Router\Bindable}.
     */
    public static function resolve(string $input, array $relations = []): ?static
    {
        // @phpstan-ignore-next-line
        return static::queryBuilder()->get($input, $relations);
    }

    /**
     * Gets a model instance by its ID, optionally loading the given relationships.
     */
    public static function get(string|int|PrimaryKey $id, array $relations = []): ?static
    {
        // @phpstan-ignore-next-line
        return static::queryBuilder()->get($id, $relations);
    }

    /**
     * Gets all records from the model's table.
     *
     * @return static[]
     */
    public static function all(array $relations = []): array
    {
        return static::queryBuilder()->all($relations);
    }

    /**
     * Finds records based on their columns.
     *
     * **Example**
     * ```php
     * MagicUser::find(name: 'Frieren');
     * ```
     *
     * @return SelectQueryBuilder<static>
     */
    public static function find(mixed ...$conditions): SelectQueryBuilder
    {
        return static::queryBuilder()->find(...$conditions);
    }

    /**
     * Creates a new model instance and persists it to the database.
     *
     * **Example**
     * ```php
     * MagicUser::create(name: 'Frieren', kind: Kind::ELF);
     * ```
     *
     * @return static
     */
    public static function create(mixed ...$params): static
    {
        // @phpstan-ignore-next-line
        return static::queryBuilder()->create(...$params);
    }

    /**
     * Finds an existing model instance or creates a new one if it doesn't exist, without persisting it to the database.
     *
     * **Example**
     * ```php
     * $model = MagicUser::findOrNew(
     *     find: ['name' => 'Frieren'],
     *     update: ['kind' => Kind::ELF],
     * );
     * ```
     *
     * @param array<string,mixed> $find Properties to search for in the existing model.
     * @param array<string,mixed> $update Properties to update or set on the model if it is found or created.
     * @return static
     */
    public static function findOrNew(array $find, array $update): static
    {
        // @phpstan-ignore-next-line
        return static::queryBuilder()->findOrNew($find, $update);
    }

    /**
     * Finds an existing model instance or creates a new one if it doesn't exist, and persists it to the database.
     *
     * **Example**
     * ```php
     * $model = MagicUser::findOrNew(
     *     find: ['name' => 'Frieren'],
     *    update: ['kind' => Kind::ELF],
     * );
     * ```
     *
     * @param array<string,mixed> $find Properties to search for in the existing model.
     * @param array<string,mixed> $update Properties to update or set on the model if it is found or created.
     */
    public static function updateOrCreate(array $find, array $update): static
    {
        // @phpstan-ignore-next-line
        return static::queryBuilder()->updateOrCreate($find, $update);
    }

    /**
     * Applies the given scope to a new query for this model.
     *
     * @return QueryBuilder<static>
     */
    public static function scope(QueryScope $scope): QueryBuilder
    {
        return static::queryBuilder()->scope($scope);
    }

    /**
     * Adds a `WHERE` condition using a raw statement.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function where(string $statement, mixed ...$bindings): SelectQueryBuilder
    {
        return static::select()->where($statement, ...$bindings);
    }

    /**
     * Adds a `WHERE` condition on the given field.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereField(string $field, mixed $value, string|WhereOperator $operator = WhereOperator::EQUALS): SelectQueryBuilder
    {
        return static::select()->whereField($field, $value, $operator);
    }

    /**
     * Adds a raw `WHERE` condition.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereRaw(string $statement, mixed ...$bindings): SelectQueryBuilder
    {
        return static::select()->whereRaw($statement, ...$bindings);
    }

    /**
     * Adds a grouped `WHERE` condition.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereGroup(Closure $callback): SelectQueryBuilder
    {
        return static::select()->whereGroup($callback);
    }

    /**
     * Adds a `WHERE IN` condition on the given field.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereIn(string $field, string|UnitEnum|array|ArrayAccess $values): SelectQueryBuilder
    {
        return static::select()->whereIn($field, $values);
    }

    /**
     * Adds a `WHERE NOT IN` condition on the given field.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereNotIn(string $field, string|UnitEnum|array|ArrayAccess $values): SelectQueryBuilder
    {
        return static::select()->whereNotIn($field, $values);
    }

    /**
     * Adds a `WHERE BETWEEN` condition on the given field.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereBetween(string $field, DateTimeInterface|string|float|int|Countable $min, DateTimeInterface|string|float|int|Countable $max): SelectQueryBuilder
    {
        return static::select()->whereBetween($field, $min, $max);
    }

    /**
     * Adds a `WHERE NOT BETWEEN` condition on the given field.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereNotBetween(string $field, DateTimeInterface|string|float|int|Countable $min, DateTimeInterface|string|float|int|Countable $max): SelectQueryBuilder
    {
        return static::select()->whereNotBetween($field, $min, $max);
    }

    /**
     * Adds a `WHERE NULL` condition on the given field.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereNull(string $field): SelectQueryBuilder
    {
        return static::select()->whereNull($field);
    }

    /**
     * Adds a `WHERE NOT NULL` condition on the given field.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereNotNull(string $field): SelectQueryBuilder
    {
        return static::select()->whereNotNull($field);
    }

    /**
     * Adds a `WHERE NOT` condition on the given field.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereNot(string $field, mixed $value): SelectQueryBuilder
    {
        return static::select()->whereNot($field, $value);
    }

    /**
     * Adds a `WHERE LIKE` condition on the given field.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereLike(string $field, string $value): SelectQueryBuilder
    {
        return static::select()->whereLike($field, $value);
    }

    /**
     * Adds a `WHERE NOT LIKE` condition on the given field.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereNotLike(string $field, string $value): SelectQueryBuilder
    {
        return static::select()->whereNotLike($field, $value);
    }

    /**
     * Adds a `WHERE` condition for records from today.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereToday(string $field): SelectQueryBuilder
    {
        return static::select()->whereToday($field);
    }

    /**
     * Adds a `WHERE` condition for records from yesterday.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereYesterday(string $field): SelectQueryBuilder
    {
        return static::select()->whereYesterday($field);
    }

    /**
     * Adds a `WHERE` condition for records from this week.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereThisWeek(string $field): SelectQueryBuilder
    {
        return static::select()->whereThisWeek($field);
    }

    /**
     * Adds a `WHERE` condition for records from last week.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereLastWeek(string $field): SelectQueryBuilder
    {
        return static::select()->whereLastWeek($field);
    }

    /**
     * Adds a `WHERE` condition for records from this month.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereThisMonth(string $field): SelectQueryBuilder
    {
        return static::select()->whereThisMonth($field);
    }

    /**
     * Adds a `WHERE` condition for records from last month.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereLastMonth(string $field): SelectQueryBuilder
    {
        return static::select()->whereLastMonth($field);
    }

    /**
     * Adds a `WHERE` condition for records from this year.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereThisYear(string $field): SelectQueryBuilder
    {
        return static::select()->whereThisYear($field);
    }

    /**
     * Adds a `WHERE` condition for records from last year.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereLastYear(string $field): SelectQueryBuilder
    {
        return static::select()->whereLastYear($field);
    }

    /**
     * Adds a `WHERE` condition for records which specified field is after a specific date.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereAfter(string $field, DateTimeInterface|string $date): SelectQueryBuilder
    {
        return static::select()->whereAfter($field, $date);
    }

    /**
     * Adds a `WHERE` condition for records which specified field is before a specific date.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereBefore(string $field, DateTimeInterface|string $date): SelectQueryBuilder
    {
        return static::select()->whereBefore($field, $date);
    }

    /**
     * Adds a `WHERE EXISTS` condition for a relation.
     *
     * @phpstan-param (?Closure(SelectQueryBuilder): void) $callback
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereHas(
        string $relation,
        ?Closure $callback = null,
        string|WhereOperator $operator = WhereOperator::GREATER_THAN_OR_EQUAL,
        int $count = 1,
    ): SelectQueryBuilder {
        return static::select()->whereHas($relation, $callback, $operator, $count);
    }

    /**
     * Adds a `WHERE NOT EXISTS` condition for a relation.
     *
     * @phpstan-param (?Closure(SelectQueryBuilder): void) $callback
     *
     * @return SelectQueryBuilder<static>
     */
    public static function whereDoesntHave(
        string $relation,
        ?Closure $callback = null,
    ): SelectQueryBuilder {
        return static::select()->whereDoesntHave($relation, $callback);
    }

    /**
     * Returns the first record of this model's table.
     *
     * @return static|null
     */
    public static function first(mixed ...$bindings): mixed
    {
        return static::select()->first(...$bindings);
    }

    /**
     * Adds an `ORDER BY` statement to the query.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function orderBy(string $field, Direction $direction = Direction::ASC): SelectQueryBuilder
    {
        return static::select()->orderBy($field, $direction);
    }

    /**
     * Adds a raw `ORDER BY` statement to the query.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function orderByRaw(string $statement): SelectQueryBuilder
    {
        return static::select()->orderByRaw($statement);
    }

    /**
     * Adds a `GROUP BY` statement to the query.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function groupBy(string $statement): SelectQueryBuilder
    {
        return static::select()->groupBy($statement);
    }

    /**
     * Adds a `HAVING` statement to the query.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function having(string $statement, mixed ...$bindings): SelectQueryBuilder
    {
        return static::select()->having($statement, ...$bindings);
    }

    /**
     * Limits the amount of records returned by the query.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function limit(int $limit): SelectQueryBuilder
    {
        return static::select()->limit($limit);
    }

    /**
     * Offsets the records returned by the query.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function offset(int $offset): SelectQueryBuilder
    {
        return static::select()->offset($offset);
    }

    /**
     * Adds the given raw join statements to the query.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function join(string ...$joins): SelectQueryBuilder
    {
        return static::select()->join(...$joins);
    }

    /**
     * Eager-loads the given relations.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function with(string ...$relations): SelectQueryBuilder
    {
        return static::select()->with(...$relations);
    }

    /**
     * Includes the given fields in the query.
     *
     * @return SelectQueryBuilder<static>
     */
    public static function include(string ...$fields): SelectQueryBuilder
    {
        return static::select()->include(...$fields);
    }

    /**
     * Paginates the records of this model's table.
     */
    public static function paginate(int $itemsPerPage = 20, int $currentPage = 1, int $maxLinks = 10): PaginatedData
    {
        return static::select()->paginate($itemsPerPage, $currentPage, $maxLinks);
    }

    /**
     * Paginates the records of this model's table, without counting the total amount of records.
     */
    public static function simplePaginate(int $itemsPerPage = 20, int $currentPage = 1): SimplePaginatedData
    {
        return static::select()->simplePaginate($itemsPerPage, $currentPage);
    }

    /**
     * Chunks the records of this model's table, passing each chunk to the given closure.
     */
    public static function chunk(Closure $closure, int $amountPerChunk = 200): void
    {
        static::select()->chunk($closure, $amountPerChunk);
    }

    /**
     * Refreshes the model instance with the latest data from the database.
     */
    public function refresh(): static
    {
        $model = inspect($this);

        $loadedRelations = $model
            ->getRelations()
            ->filter($model->isRelationLoaded(...));

        $primaryKeyProperty = $model->getPrimaryKeyProperty();
        $primaryKeyValue = $primaryKeyProperty->getValue($this);

        $new = static::queryBuilder()
            ->onDatabase($this->onDatabase)
            ->select()
            ->with(...$loadedRelations->map(fn (Relation $relation) => $relation->name))
            ->get($primaryKeyValue);

        foreach ($loadedRelations as $relation) {
            $relation->property->setValue(
                object: $this,
                value: $relation->property->getValue($new),
            );
        }

        foreach ($model->getValueFields() as $property) {
            $property->setValue(
                object: $this,
                value: $property->getValue($new),
            );
        }

        return $this;
    }

    /**
     * Returns a query builder scoped to a collection relation on this model.
     */
    public function query(string $relation): QueryBuilder
    {
        $model = inspect(model: $this);

        if (! $model->hasPrimaryKey() || ! $model->getPrimaryKeyProperty()->isInitialized(object: $this)) {
            throw new PrimaryKeyWasNotInitialized(model: $model->getName());
        }

        $resolved = $model->getRelation(name: $relation);

        if (! $resolved instanceof Relation) {
            throw new PropertyWasNotARelation(property: $relation, model: $model->getName());
        }

        return $resolved->query(
            primaryKey: $model->getPrimaryKeyValue(),
            onDatabase: $this->onDatabase,
        );
    }

    /**
     * Loads the specified relations on the model instance.
     */
    public function load(string ...$relations): static
    {
        $model = inspect($this);

        $primaryKeyProperty = $model->getPrimaryKeyProperty();
        $primaryKeyValue = $primaryKeyProperty->getValue($this);

        $new = static::queryBuilder()
            ->onDatabase($this->onDatabase)
            ->get($primaryKeyValue, $relations);

        $fieldsToUpdate = arr($relations)
            ->map(fn (string $relation) => str($relation)->before('.')->toString())
            ->unique();

        foreach ($fieldsToUpdate as $fieldToUpdate) {
            $this->{$fieldToUpdate} = $new->{$fieldToUpdate};
        }

        return $this;
    }

    /**
     * Saves the model to the database. If the model has no primary key, this method always inserts.
     */
    public function save(): static
    {
        $model = inspect($this);
        $model->validate(...inspect($this)->getPropertyValues());

        // Models without primary keys always insert
        if (! $model->hasPrimaryKey()) {
            query($this::class)
                ->onDatabase($this->onDatabase)
                ->insert($this)
                ->execute();

            return $this;
        }

        $primaryKeyProperty = $model->getPrimaryKeyProperty();
        $isInitialized = $primaryKeyProperty->isInitialized($this);
        $primaryKeyValue = $isInitialized ? $primaryKeyProperty->getValue($this) : null;

        // If there is a primary key property but it's not set, we insert the model
        // to generate the id and populate the model instance with it
        if ($primaryKeyValue === null) {
            $id = query($this::class)
                ->onDatabase($this->onDatabase)
                ->insert($this)
                ->execute();

            if (! $model->hasUuidPrimaryKey()) {
                $primaryKeyProperty->setValue($this, $id);
            }

            return $this;
        }

        // Is the model was already saved, we update it
        query($this)
            ->onDatabase($this->onDatabase)
            ->update(...inspect($this)->getPropertyValues())
            ->execute();

        return $this;
    }

    /**
     * Updates the specified columns and persist the model to the database.
     */
    public function update(mixed ...$params): static
    {
        $model = inspect($this);

        $model->validate(...$params);

        query($this)
            ->onDatabase($this->onDatabase)
            ->update(...$params)
            ->whereField($model->getPrimaryKey(), $model->getPrimaryKeyValue())
            ->execute();

        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Deletes this model from the database.
     */
    public function delete(): void
    {
        query($this)
            ->onDatabase($this->onDatabase)
            ->delete()
            ->build()
            ->execute();
    }

    public function __get(string $name): mixed
    {
        $property = PropertyReflector::fromParts($this, $name);

        if ($property->hasAttribute(Lazy::class)) {
            $this->load($name);

            return $property->getValue($this);
        }

        if (inspect(model: $this)->isRelation(name: $name)) {
            throw new RelationWasMissing($this, $name);
        }

        if ($property->getType()->isBuiltIn()) {
            throw new ValueWasMissing($this, $name);
        }

        throw new RelationWasMissing($this, $name);
    }
}
