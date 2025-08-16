<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\InsertQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Exceptions\RelationWasMissing;
use Tempest\Database\Exceptions\ValueWasMissing;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\SkipValidation;

trait IsDatabaseModel
{
    #[SkipValidation]
    public PrimaryKey $id;

    #[Virtual]
    public int|string $bindingValue {
        get => $this->id->value;
    }

    /**
     * Returns a builder for selecting records using this model's table.
     *
     * @return SelectQueryBuilder<self>
     */
    public static function select(): SelectQueryBuilder
    {
        return query(self::class)->select();
    }

    /**
     * Returns a builder for inserting records using this model's table.
     *
     * @return InsertQueryBuilder<self>
     */
    public static function insert(): InsertQueryBuilder
    {
        return query(self::class)->insert();
    }

    /**
     * Returns a builder for counting records using this model's table.
     *
     * @return CountQueryBuilder<self>
     */
    public static function count(): CountQueryBuilder
    {
        return query(self::class)->count();
    }

    /**
     * Creates a new instance of this model without persisting it to the database.
     */
    public static function new(mixed ...$params): self
    {
        return query(self::class)->new(...$params);
    }

    /**
     * Finds a model instance by its ID.
     */
    public static function findById(string|int|PrimaryKey $id): self
    {
        return self::get($id);
    }

    /**
     * Finds a model instance by its ID.
     */
    public static function resolve(string|int|PrimaryKey $id): self
    {
        return query(self::class)->resolve($id);
    }

    /**
     * Gets a model instance by its ID, optionally loading the given relationships.
     */
    public static function get(string|int|PrimaryKey $id, array $relations = []): ?self
    {
        return query(self::class)->get($id, $relations);
    }

    /**
     * Gets all records from the model's table.
     *
     * @return self[]
     */
    public static function all(array $relations = []): array
    {
        return query(self::class)->all($relations);
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
        return query(self::class)->find(...$conditions);
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
        return query(self::class)->create(...$params);
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
        return query(self::class)->findOrNew($find, $update);
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
     * @return TModel
     */
    public static function updateOrCreate(array $find, array $update): self
    {
        return query(self::class)->updateOrCreate($find, $update);
    }

    /**
     * Refreshes the model instance with the latest data from the database.
     */
    public function refresh(): self
    {
        $model = inspect($this);

        if (! $model->hasPrimaryKey()) {
            throw Exceptions\ModelDidNotHavePrimaryColumn::neededForMethod($this, 'refresh');
        }

        $relations = [];

        foreach (new ClassReflector($this)->getPublicProperties() as $property) {
            if (! $property->isInitialized($this) || ! $property->getValue($this)) {
                continue;
            }

            if (! $model->isRelation($property->getName())) {
                continue;
            }

            $relations[] = $property->getName();
        }

        $this->load(...$relations);

        return $this;
    }

    /**
     * Loads the specified relations on the model instance.
     */
    public function load(string ...$relations): self
    {
        $model = inspect($this);

        if (! $model->hasPrimaryKey()) {
            throw Exceptions\ModelDidNotHavePrimaryColumn::neededForMethod($this, 'load');
        }

        $primaryKeyProperty = $model->getPrimaryKeyProperty();
        $primaryKeyValue = $primaryKeyProperty->getValue($this);

        $new = self::get($primaryKeyValue, $relations);

        foreach (new ClassReflector($new)->getPublicProperties() as $property) {
            if ($property->hasAttribute(Virtual::class)) {
                continue;
            }

            $property->setValue($this, $property->getValue($new));
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

            $primaryKeyProperty->setValue($this, $id);

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
    public function update(mixed ...$params): self
    {
        $model = inspect($this);

        if (! $model->hasPrimaryKey()) {
            throw Exceptions\ModelDidNotHavePrimaryColumn::neededForMethod($this, 'update');
        }

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
