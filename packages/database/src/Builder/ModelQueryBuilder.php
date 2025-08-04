<?php

declare(strict_types=1);

namespace Tempest\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\DeleteQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\InsertQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\UpdateQueryBuilder;
use Tempest\Database\Exceptions\ModelDidNotHavePrimaryColumn;
use Tempest\Database\PrimaryKey;

use function Tempest\Database\inspect;
use function Tempest\Database\query;
use function Tempest\make;

/**
 * @template TModel of object
 */
final readonly class ModelQueryBuilder
{
    public function __construct(
        /** @var class-string<TModel> */
        private string $model,
    ) {}

    /**
     * Returns a builder for selecting records using this model's table.
     *
     * **Example**
     * ```php
     * model(User::class)
     *   ->select('id', 'username', 'email')
     *   ->execute();
     * ```
     *
     * @return SelectQueryBuilder<TModel>
     */
    public function select(string ...$columns): SelectQueryBuilder
    {
        return query($this->model)->select(...$columns);
    }

    /**
     * Returns a builder for inserting records using this model's table.
     *
     * **Example**
     * ```php
     * model(User::class)
     *   ->insert(username: 'Frieren')
     *   ->execute();
     * ```
     *
     * @return InsertQueryBuilder<TModel>
     */
    public function insert(mixed ...$values): InsertQueryBuilder
    {
        return query($this->model)->insert(...$values);
    }

    /**
     * Returns a builder for updating records using this model's table.
     *
     * **Example**
     * ```php
     * model(User::class)
     *   ->update(is_admin: true)
     *   ->whereIn('id', [1, 2, 3])
     *   ->execute();
     * ```
     *
     * @return UpdateQueryBuilder<TModel>
     */
    public function update(mixed ...$values): UpdateQueryBuilder
    {
        return query($this->model)->update(...$values);
    }

    /**
     * Returns a builder for deleting records using this model's table.
     *
     * **Example**
     * ```php
     * model(User::class)
     *     ->delete()
     *     ->where(name: 'Frieren')
     *     ->execute();
     * ```
     *
     * @return DeleteQueryBuilder<TModel>
     */
    public function delete(): DeleteQueryBuilder
    {
        return query($this->model)->delete();
    }

    /**
     * Returns a builder for counting records using this model's table.
     *
     * **Example**
     * ```php
     * model(User::class)->count()->execute();
     * ```
     *
     * @return CountQueryBuilder<TModel>
     */
    public function count(): CountQueryBuilder
    {
        return query($this->model)->count();
    }

    /**
     * Creates a new instance of this model without persisting it to the database.
     *
     * **Example**
     * ```php
     * model(User::class)->new(name: 'Frieren');
     * ```
     *
     * @return TModel
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
     * model(User::class)->findById(1);
     * ```
     *
     * @return TModel
     */
    public function findById(string|int|PrimaryKey $id): object
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
     * model(User::class)->resolve(1);
     * ```
     *
     * @return TModel
     */
    public function resolve(string|int|PrimaryKey $id): object
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
     * model(User::class)->get(1);
     * ```
     *
     * @return TModel|null
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

        return $this->select()
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
        return $this->select()
            ->with(...$relations)
            ->all();
    }

    /**
     * Finds records based on their columns.
     *
     * **Example**
     * ```php
     * model(User::class)->find(name: 'Frieren');
     * ```
     *
     * @return SelectQueryBuilder<TModel>
     */
    public function find(mixed ...$conditions): SelectQueryBuilder
    {
        $query = $this->select();

        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }

        return $query;
    }

    /**
     * Creates a new model instance and persists it to the database.
     *
     * **Example**
     * ```php
     * model(User::class)->create(name: 'Frieren', kind: Kind::ELF);
     * ```
     *
     * @return TModel
     */
    public function create(mixed ...$params): object
    {
        inspect($this->model)->validate(...$params);

        $model = $this->new(...$params);

        $id = query($this->model)
            ->insert($model)
            ->build()
            ->execute();

        $inspector = inspect($this->model);
        $primaryKeyProperty = $inspector->getPrimaryKeyProperty();

        if ($id !== null && $primaryKeyProperty !== null) {
            $primaryKeyName = $primaryKeyProperty->getName();
            $model->{$primaryKeyName} = new PrimaryKey($id);
        }

        return $model;
    }

    /**
     * Finds an existing model instance or creates a new one if it doesn't exist, without persisting it to the database.
     *
     * **Example**
     * ```php
     * $model = model(User::class)->findOrNew(
     *     find: ['name' => 'Frieren'],
     *     update: ['kind' => Kind::ELF],
     * );
     * ```
     *
     * @param array<string,mixed> $find Properties to search for in the existing model.
     * @param array<string,mixed> $update Properties to update or set on the model if it is found or created.
     * @return TModel
     */
    public function findOrNew(array $find, array $update): object
    {
        $existing = $this->select();

        foreach ($find as $key => $value) {
            $existing = $existing->where($key, $value);
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
     * $model = model(User::class)->updateOrCreate(
     *     find: ['name' => 'Frieren'],
     *    update: ['kind' => Kind::ELF],
     * );
     * ```
     *
     * @param array<string,mixed> $find Properties to search for in the existing model.
     * @param array<string,mixed> $update Properties to update or set on the model if it is found or created.
     * @return TModel
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
            return $this->create(...$update);
        }

        query($model)
            ->update(...$update)
            ->execute();

        foreach ($update as $key => $value) {
            $model->{$key} = $value;
        }

        return $model;
    }
}
