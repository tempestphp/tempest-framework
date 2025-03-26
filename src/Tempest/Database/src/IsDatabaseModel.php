<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Builder\Queries\CreateModelQuery;
use Tempest\Database\Builder\Queries\SelectModelQuery;
use Tempest\Database\Builder\Queries\UpdateModelQuery;
use Tempest\Database\Exceptions\MissingRelation;
use Tempest\Database\Exceptions\MissingValue;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use function Tempest\get;
use function Tempest\make;
use function Tempest\map;

trait IsDatabaseModel
{
    public ?Id $id = null;

    public static function new(mixed ...$params): self
    {
        return make(self::class)->from($params);
    }

    public static function resolve(string $input): self
    {
        return self::get(new Id($input));
    }

    /**
     * @return \Tempest\Database\Builder\Queries\SelectModelQuery<self>
     */
    public static function select(): SelectModelQuery
    {
        return ModelQuery::select(self::class);
    }

    /** @return self[] */
    public static function all(array $relations = []): array
    {
        return self::select()
            ->with(...$relations)
            ->all();
    }

    public static function get(Id $id, array $relations = []): ?self
    {
        return self::select()
            ->with(...$relations)
            ->get($id);
    }

    public static function find(mixed ...$conditions): SelectModelQuery
    {
        $query = self::select();

        array_walk($conditions, fn ($value, $column) => $query->whereField($column, $value));

        return $query;
    }

    public static function create(mixed ...$params): self
    {
        return self::new(...$params)->save();
    }

    public static function updateOrNew(array $find, array $update): self
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
        return self::updateOrNew($find, $update)->save();
    }

    public function __get(string $name): mixed
    {
        $property = PropertyReflector::fromParts($this, $name);

        if ($property->hasAttribute(Lazy::class)) {
            $this->load($name);

            return $property->getValue($this);
        }

        $type = $property->getType();

        if ($type->isIterable()) {
            throw new MissingRelation($this, $name);
        }

        if ($type->isBuiltIn()) {
            throw new MissingValue($this, $name);
        }

        throw new MissingRelation($this, $name);
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
        $serializerFactory = get(SerializerFactory::class);

        if ($this->id === null) {
            $query = new CreateModelQuery($serializerFactory)->build($this);
        } else {
            $query = new UpdateModelQuery($serializerFactory)->build($this);
        }

        $id = $query->execute();

        $this->id = $id;

        return $this;
    }

    public function update(mixed ...$params): self
    {
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }

        return $this->save();
    }

    public function delete(): void
    {
        $table = new ModelDefinition($this)->getTableDefinition();

        $query = new Query(
            sprintf(
                'DELETE FROM %s WHERE `id` = :id',
                $table,
            ),
            [
                'id' => $this->id->id,
            ],
        );

        $query->execute();
    }
}
