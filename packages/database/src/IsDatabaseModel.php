<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\InsertQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Exceptions\PrimaryKeyWasNotInitialized;
use Tempest\Database\Exceptions\PropertyWasNotARelation;
use Tempest\Database\Exceptions\RelationWasMissing;
use Tempest\Database\Exceptions\ValueWasMissing;
use Tempest\Reflection\PropertyReflector;
use Tempest\Router\IsBindingValue;
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
     * @return QueryBuilder<self>
     */
    public static function on(string|UnitEnum|null $databaseTag): QueryBuilder
    {
        return self::queryBuilder()->onDatabase(databaseTag: $databaseTag);
    }

    /**
     * Targets a specific database connection for this model instance.
     */
    public function onDatabase(string|UnitEnum|null $databaseTag): self
    {
        $clone = clone $this;

        $clone->onDatabase = $databaseTag;

        return $clone;
    }

    /** @return QueryBuilder<self> */
    protected static function queryBuilder(): QueryBuilder
    {
        return query(self::class);
    }

    /**
     * Returns a builder for selecting records using this model's table.
     *
     * @return SelectQueryBuilder<self>
     */
    public static function select(): SelectQueryBuilder
    {
        return self::queryBuilder()->select();
    }

    /**
     * Returns a builder for inserting records using this model's table.
     *
     * @return InsertQueryBuilder<self>
     */
    public static function insert(): InsertQueryBuilder
    {
        return self::queryBuilder()->insert();
    }

    /**
     * Returns a builder for counting records using this model's table.
     *
     * @return CountQueryBuilder<self>
     */
    public static function count(): CountQueryBuilder
    {
        return self::queryBuilder()->count();
    }

    /**
     * Executes an aggregate query and returns the sum of the given column.
     */
    public static function sum(string $column): int|float
    {
        return self::queryBuilder()->sum(column: $column);
    }

    /**
     * Executes an aggregate query and returns the average of the given column.
     */
    public static function avg(string $column): float
    {
        return self::queryBuilder()->avg(column: $column);
    }

    /**
     * Executes an aggregate query and returns the maximum value of the given column.
     */
    public static function max(string $column): mixed
    {
        return self::queryBuilder()->max(column: $column);
    }

    /**
     * Executes an aggregate query and returns the minimum value of the given column.
     */
    public static function min(string $column): mixed
    {
        return self::queryBuilder()->min(column: $column);
    }

    /**
     * Creates a new instance of this model without persisting it to the database.
     */
    public static function new(mixed ...$params): self
    {
        return self::queryBuilder()->new(...$params);
    }

    /**
     * Finds a model instance by its ID.
     */
    public static function findById(string|int|PrimaryKey $id): ?self
    {
        return self::get($id);
    }

    /**
     * Finds a model instance by its ID. Use through {@see \Tempest\Router\Bindable}.
     */
    public static function resolve(string $input, array $relations = []): ?static
    {
        // @phpstan-ignore-next-line
        return self::queryBuilder()->get($input, $relations);
    }

    /**
     * Gets a model instance by its ID, optionally loading the given relationships.
     */
    public static function get(string|int|PrimaryKey $id, array $relations = []): ?self
    {
        return self::queryBuilder()->get($id, $relations);
    }

    /**
     * Gets all records from the model's table.
     *
     * @return self[]
     */
    public static function all(array $relations = []): array
    {
        return self::queryBuilder()->all($relations);
    }

    /**
     * Finds records based on their columns.
     *
     * **Example**
     * ```php
     * MagicUser::find(name: 'Frieren');
     * ```
     *
     * @return SelectQueryBuilder<self>
     */
    public static function find(mixed ...$conditions): SelectQueryBuilder
    {
        return self::queryBuilder()->find(...$conditions);
    }

    /**
     * Creates a new model instance and persists it to the database.
     *
     * **Example**
     * ```php
     * MagicUser::create(name: 'Frieren', kind: Kind::ELF);
     * ```
     *
     * @return self
     */
    public static function create(mixed ...$params): self
    {
        return self::queryBuilder()->create(...$params);
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
     * @return self
     */
    public static function findOrNew(array $find, array $update): self
    {
        return self::queryBuilder()->findOrNew($find, $update);
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
    public static function updateOrCreate(array $find, array $update): self
    {
        return self::queryBuilder()->updateOrCreate($find, $update);
    }

    /**
     * Refreshes the model instance with the latest data from the database.
     */
    public function refresh(): self
    {
        $model = inspect($this);

        $loadedRelations = $model
            ->getRelations()
            ->filter($model->isRelationLoaded(...));

        $primaryKeyProperty = $model->getPrimaryKeyProperty();
        $primaryKeyValue = $primaryKeyProperty->getValue($this);

        $new = self::queryBuilder()
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
    public function load(string ...$relations): self
    {
        $model = inspect($this);

        $primaryKeyProperty = $model->getPrimaryKeyProperty();
        $primaryKeyValue = $primaryKeyProperty->getValue($this);

        $new = self::queryBuilder()
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
    public function save(): self
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
    public function update(mixed ...$params): self
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
