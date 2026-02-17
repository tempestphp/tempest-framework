<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\InsertQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Exceptions\RelationWasMissing;
use Tempest\Database\Exceptions\ValueWasMissing;
use Tempest\Reflection\PropertyReflector;
use Tempest\Router\IsBindingValue;
use Tempest\Validation\SkipValidation;

use function Tempest\Support\arr;
use function Tempest\Support\str;

trait IsDatabaseModel
{
    #[IsBindingValue, SkipValidation]
    public PrimaryKey $id;

    /**
     * @return QueryBuilder<static>
     */
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
     * Creates a new instance of this model without persisting it to the database.
     */
    public static function new(mixed ...$params): static
    {
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
     * Finds a model instance by its ID. Use through {@see Tempest\Router\Bindable}.
     */
    public static function resolve(string $input): static
    {
        return static::queryBuilder()->resolve($input);
    }

    /**
     * Gets a model instance by its ID, optionally loading the given relationships.
     */
    public static function get(string|int|PrimaryKey $id, array $relations = []): ?static
    {
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
        return static::queryBuilder()->updateOrCreate($find, $update);
    }

    /**
     * Refreshes the model instance with the latest data from the database.
     */
    public function refresh(): static
    {
        $model = inspect($this);

        $loadedRelations = $model
            ->getRelations()
            ->filter(fn (Relation $relation) => $model->isRelationLoaded($relation));

        $primaryKeyProperty = $model->getPrimaryKeyProperty();
        $primaryKeyValue = $primaryKeyProperty->getValue($this);

        $new = static::select()
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
     * Loads the specified relations on the model instance.
     */
    public function load(string ...$relations): static
    {
        $model = inspect($this);

        $primaryKeyProperty = $model->getPrimaryKeyProperty();
        $primaryKeyValue = $primaryKeyProperty->getValue($this);

        $new = static::get($primaryKeyValue, $relations);

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
                ->insert($this)
                ->execute();

            if (! $model->hasUuidPrimaryKey()) {
                $primaryKeyProperty->setValue($this, $id);
            }

            return $this;
        }

        // Is the model was already save, we update it
        query($this)
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

        $type = $property->getType();

        if ($type->isRelation()) {
            throw new RelationWasMissing($this, $name);
        }

        if ($type->isBuiltIn()) {
            throw new ValueWasMissing($this, $name);
        }

        throw new RelationWasMissing($this, $name);
    }
}
