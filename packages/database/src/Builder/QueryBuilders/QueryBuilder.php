<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\OnDatabase;
use Tempest\Database\PrimaryKey;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\PropertyReflector;

use function Tempest\Container\get;
use function Tempest\Database\inspect;
use function Tempest\Database\query;
use function Tempest\Mapper\make;
use function Tempest\Support\arr;

/** @template TModel */
final class QueryBuilder
{
    use OnDatabase;

    /** @var QueryScope[] */
    private array $scopes = [];

    /** @param class-string<TModel>|TModel|string $model */
    public function __construct(
        private readonly string|object $model,
    ) {}

    /**
     * Adds a scope that will be applied to any query builder created from this instance.
     *
     * @return self<TModel>
     */
    public function scope(QueryScope $scope): self
    {
        $this->scopes[] = $scope;

        return $this;
    }

    /**
     * Creates a `SELECT` query builder for retrieving records from the database.
     *
     * **Example**
     * ```php
     * query(User::class)
     *   ->select('id', 'username', 'email')
     *   ->execute();
     * ```
     *
     * @return SelectQueryBuilder<TModel>
     */
    public function select(string ...$columns): SelectQueryBuilder
    {
        return new SelectQueryBuilder(
            model: $this->model,
            fields: $columns !== [] ? arr($columns)->unique() : null,
        )
            ->onDatabase(databaseTag: $this->onDatabase)
            ->applyScopes(scopes: $this->scopes);
    }

    /**
     * Creates an `INSERT` query builder for adding new records to the database.
     *
     * **Example**
     * ```php
     * query(User::class)
     *   ->insert(username: 'Frieren')
     *   ->execute();
     * ```
     *
     * @return InsertQueryBuilder<TModel>
     */
    public function insert(mixed ...$values): InsertQueryBuilder
    {
        if (! array_is_list($values)) {
            $values = [$values];
        }

        return new InsertQueryBuilder(
            model: $this->model,
            rows: $values,
            serializerFactory: get(SerializerFactory::class),
        )->onDatabase($this->onDatabase);
    }

    /**
     * Creates an `UPDATE` query builder for modifying existing records in the database.
     *
     * **Example**
     * ```php
     * query(User::class)
     *   ->update(is_admin: true)
     *   ->whereIn('id', [1, 2, 3])
     *   ->execute();
     * ```
     *
     * @return UpdateQueryBuilder<TModel>
     */
    public function update(mixed ...$values): UpdateQueryBuilder
    {
        return new UpdateQueryBuilder(
            model: $this->model,
            values: $values,
            serializerFactory: get(SerializerFactory::class),
        )
            ->onDatabase(databaseTag: $this->onDatabase)
            ->applyScopes(scopes: $this->scopes);
    }

    /**
     * Creates a `DELETE` query builder for removing records from the database.
     *
     * **Example**
     * ```php
     * query(User::class)
     *     ->delete()
     *     ->where(name: 'Frieren')
     *     ->execute();
     * ```
     *
     * @return DeleteQueryBuilder<TModel>
     */
    public function delete(): DeleteQueryBuilder
    {
        return new DeleteQueryBuilder(model: $this->model)
            ->onDatabase(databaseTag: $this->onDatabase)
            ->applyScopes(scopes: $this->scopes);
    }

    /**
     * Creates a `COUNT` query builder for counting records in the database.
     *
     * **Example**
     * ```php
     * query(User::class)->count()->execute();
     * ```
     *
     * @return CountQueryBuilder<TModel>
     */
    public function count(?string $column = null): CountQueryBuilder
    {
        return new CountQueryBuilder(
            model: $this->model,
            column: $column,
        )
            ->onDatabase(databaseTag: $this->onDatabase)
            ->applyScopes(scopes: $this->scopes);
    }

    /**
     * Executes an aggregate query and returns the sum of the given column.
     *
     * **Example**
     * ```php
     * query(User::class)->sum('price');
     * ```
     */
    public function sum(string $column): int|float
    {
        return $this->select()->onDatabase(databaseTag: $this->onDatabase)->sum(column: $column);
    }

    /**
     * Executes an aggregate query and returns the average of the given column.
     *
     * **Example**
     * ```php
     * query(User::class)->avg('price');
     * ```
     */
    public function avg(string $column): float
    {
        return $this->select()->onDatabase(databaseTag: $this->onDatabase)->avg(column: $column);
    }

    /**
     * Executes an aggregate query and returns the maximum value of the given column.
     *
     * **Example**
     * ```php
     * query(User::class)->max('price');
     * ```
     */
    public function max(string $column): mixed
    {
        return $this->select()->onDatabase(databaseTag: $this->onDatabase)->max(column: $column);
    }

    /**
     * Executes an aggregate query and returns the minimum value of the given column.
     *
     * **Example**
     * ```php
     * query(User::class)->min('price');
     * ```
     */
    public function min(string $column): mixed
    {
        return $this->select()->onDatabase(databaseTag: $this->onDatabase)->min(column: $column);
    }

    /**
     * Creates a new instance of this model without persisting it to the database.
     *
     * **Example**
     * ```php
     * query(User::class)->new(name: 'Frieren');
     * ```
     *
     * @return TModel|object<TModel>
     */
    public function new(mixed ...$params): object
    {
        return make($this->model)->from($params);
    }

    /**
     * Finds a model instance by its ID.
     *
     * **Example**
     * ```php
     * query(User::class)->findById(1);
     * ```
     *
     * @return TModel|object<TModel>|null
     */
    public function findById(string|int|PrimaryKey $id): ?object
    {
        if (! inspect($this->model)->hasPrimaryKey()) {
            throw ModelDidNotHavePrimaryColumn::neededForMethod($this->model, 'findById');
        }

        return $this->get($id);
    }

    /**
     * Finds a model instance by its ID.
     *
     * **Example**
     * ```php
     * query(User::class)->resolve(1);
     * ```
     *
     * @return TModel|object<TModel>|null
     */
    public function resolve(string|int|PrimaryKey $id): ?object
    {
        if (! inspect($this->model)->hasPrimaryKey()) {
            throw ModelDidNotHavePrimaryColumn::neededForMethod($this->model, 'resolve');
        }

        return $this->get($id);
    }

    /**
     * Gets a model instance by its ID, optionally loading the given relationships.
     *
     * **Example**
     * ```php
     * query(User::class)->get(1);
     * ```
     *
     * @return TModel|object<TModel>|null
     */
    public function get(string|int|PrimaryKey $id, array $relations = []): ?object
    {
        if (! inspect($this->model)->hasPrimaryKey()) {
            throw ModelDidNotHavePrimaryColumn::neededForMethod($this->model, 'get');
        }

        $id = match (true) {
            $id instanceof PrimaryKey => $id,
            default => new PrimaryKey($id),
        };

        return $this
            ->select()
            ->with(...$relations)
            ->get($id);
    }

    /**
     * Gets all records from the model's table.
     *
     * @return TModel[]
     */
    public function all(array $relations = []): array
    {
        return $this
            ->select()
            ->with(...$relations)
            ->all();
    }

    /**
     * Finds records based on their columns.
     *
     * **Example**
     * ```php
     * query(User::class)->find(name: 'Frieren');
     * ```
     *
     * @return SelectQueryBuilder<TModel>
     */
    public function find(mixed ...$conditions): SelectQueryBuilder
    {
        $query = $this->select();

        foreach ($conditions as $field => $value) {
            $query->whereField($field, $value);
        }

        return $query;
    }

    /**
     * Creates a new model instance and persists it to the database.
     *
     * **Example**
     * ```php
     * query(User::class)->create(name: 'Frieren', kind: Kind::ELF);
     * ```
     *
     * @return TModel|object<TModel>
     */
    public function create(mixed ...$params): object
    {
        inspect($this->model)->validate(...$params);

        $model = $this->new(...$params);

        $id = $this->insert($model)->execute();

        $inspector = inspect($this->model);
        $primaryKeyProperty = $inspector->getPrimaryKeyProperty();

        if ($id instanceof PrimaryKey && $primaryKeyProperty instanceof PropertyReflector) {
            $primaryKeyName = $primaryKeyProperty->getName();

            if (! $inspector->hasUuidPrimaryKey() || $model->{$primaryKeyName} === null) {
                $model->{$primaryKeyName} = new PrimaryKey($id);
            }
        }

        return $model;
    }

    /**
     * Finds an existing model instance or creates a new one if it doesn't exist, without persisting it to the database.
     *
     * **Example**
     * ```php
     * $model = query(User::class)->findOrNew(
     *     find: ['name' => 'Frieren'],
     *     update: ['kind' => Kind::ELF],
     * );
     * ```
     *
     * @param array<string,mixed> $find Properties to search for in the existing model.
     * @param array<string,mixed> $update Properties to update or set on the model if it is found or created.
     * @return TModel|object<TModel>
     */
    public function findOrNew(array $find, array $update): object
    {
        $existing = $this->select();

        foreach ($find as $key => $value) {
            $existing = $existing->whereField($key, $value);
        }

        $model = $existing->first() ?? $this->new(...$find);

        foreach ($update as $key => $value) {
            $model->{$key} = $value;
        }

        return $model;
    }

    /**
     * Finds an existing model instance or creates a new one if it doesn't exist, and persists it to the database.
     *
     * **Example**
     * ```php
     * $model = query(User::class)->updateOrCreate(
     *     find: ['name' => 'Frieren'],
     *    update: ['kind' => Kind::ELF],
     * );
     * ```
     *
     * @param array<string,mixed> $find Properties to search for in the existing model.
     * @param array<string,mixed> $update Properties to update or set on the model if it is found or created.
     * @return TModel|object<TModel>
     */
    public function updateOrCreate(array $find, array $update): object
    {
        $inspector = inspect($this->model);

        if (! $inspector->hasPrimaryKey()) {
            throw ModelDidNotHavePrimaryColumn::neededForMethod($this->model, 'updateOrCreate');
        }

        $model = $this->findOrNew($find, $update);

        $primaryKeyProperty = $inspector->getPrimaryKeyProperty();
        $primaryKeyName = $primaryKeyProperty->getName();

        if (! isset($model->{$primaryKeyName})) {
            return $this->create(...array_merge($find, $update));
        }

        query($model)
            ->onDatabase($this->onDatabase)
            ->update(...$update)
            ->execute();

        foreach ($update as $key => $value) {
            $model->{$key} = $value;
        }

        return $model;
    }
}
