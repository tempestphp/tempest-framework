<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Exceptions\RelationWasMissing;
use Tempest\Database\Exceptions\ValueWasMissing;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\SkipValidation;

use function Tempest\make;

trait IsDatabaseModel
{
    #[SkipValidation]
    public Id $id;

    public static function new(mixed ...$params): self
    {
        return make(self::class)->from($params);
    }

    public static function resolve(string $input): static
    {
        return self::get(new Id($input));
    }

    /**
     * @return \Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder<self>
     */
    public static function select(): SelectQueryBuilder
    {
        return query(self::class)->select();
    }

    /** @return self[] */
    public static function all(array $relations = []): array
    {
        return self::select()
            ->with(...$relations)
            ->all();
    }

    public static function get(string|int|Id $id, array $relations = []): ?self
    {
        if (! ($id instanceof Id)) {
            $id = new Id($id);
        }

        return self::select()
            ->with(...$relations)
            ->get($id);
    }

    public static function find(mixed ...$conditions): SelectQueryBuilder
    {
        $query = self::select();

        array_walk($conditions, fn ($value, $column) => $query->whereField($column, $value));

        return $query;
    }

    public static function count(): CountQueryBuilder
    {
        return query(self::class)->count();
    }

    public static function create(mixed ...$params): self
    {
        model(self::class)->validate(...$params);

        $model = self::new(...$params);

        $id = query(self::class)
            ->insert($model)
            ->build()
            ->execute();

        if ($id !== null) {
            $model->id = new Id($id);
        }

        return $model;
    }

    public static function findOrNew(array $find, array $update): self
    {
        $existing = self::select()->bind(...$find);

        foreach ($find as $key => $value) {
            $existing = $existing->where("{$key} = :{$key}");
        }

        $model = $existing->first() ?? self::new(...$find);

        foreach ($update as $key => $value) {
            $model->{$key} = $value;
        }

        return $model;
    }

    public static function updateOrCreate(array $find, array $update): self
    {
        $model = self::findOrNew($find, $update);

        if (! isset($model->id)) {
            return self::create(...$update);
        }

        return $model->save();
    }

    public function refresh(): self
    {
        $model = self::find(id: $this->id)->first();
        foreach (new ClassReflector($model)->getPublicProperties() as $property) {
            $property->setValue($this, $property->getValue($model));
        }

        return $this;
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

    public function load(string ...$relations): self
    {
        $new = self::get($this->id, $relations);

        foreach (new ClassReflector($new)->getPublicProperties() as $property) {
            $property->setValue($this, $property->getValue($new));
        }

        return $this;
    }

    public function save(): self
    {
        $model = model($this);

        $model->validate(...model($this)->getPropertyValues());

        if (! isset($this->id)) {
            $query = query($this::class)->insert($this);

            $this->id = $query->execute();
        } else {
            query($this)->update(
                ...model($this)->getPropertyValues(),
            )->execute();
        }

        return $this;
    }

    public function update(mixed ...$params): self
    {
        model(self::class)->validate(...$params);

        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }

        query($this)
            ->update(...$params)
            ->build()
            ->execute();

        return $this;
    }

    public function delete(): void
    {
        query($this)
            ->delete()
            ->build()
            ->execute();
    }
}
