<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Builder\SelectModelQuery;
use Tempest\Database\Exceptions\MissingRelation;
use Tempest\Database\Exceptions\MissingValue;
use Tempest\Database\Mappers\ModelToQueryMapper;
use Tempest\Database\Mappers\QueryToModelMapper;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;

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
     * @return \Tempest\Database\Builder\SelectModelQuery<self>
     */
    public static function query(): SelectModelQuery
    {
        return ModelQuery::select(self::class);
    }

    /** @return self[] */
    public static function all(array $relations = []): array
    {
        return self::query()
            ->with(...$relations)
            ->all();
    }

    public static function get(Id $id, array $relations = []): ?self
    {
        return self::query()
            ->with(...$relations)
            ->get($id);
    }

    public static function find(mixed ...$conditions): SelectModelQuery
    {
        $query = self::query();

        array_walk($conditions, fn ($value, $column) => $query->whereField($column, $value));

        return $query;
    }

    public static function create(mixed ...$params): self
    {
        $model = self::new(...$params);

        $id = make(Query::class)->from($model)->execute();

        $model->id = $id;

        return $model;
    }

    public static function updateOrNew(array $find, array $update): self
    {
        $existing = self::query()->bind(...$find);

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
        $query = map($this)->to(Query::class);

        $id = $query->execute();

        $this->id = $id;

        return $this;
    }

    public function update(mixed ...$params): self
    {
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }

        $query = make(Query::class)->from($this);

        $query->execute();

        return $this;
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
