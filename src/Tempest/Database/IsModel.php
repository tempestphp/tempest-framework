<?php

declare(strict_types=1);

namespace Tempest\Database;

use BackedEnum;
use ReflectionClass;
use ReflectionProperty;
use function Tempest\attribute;
use Tempest\Database\Builder\FieldName;
use Tempest\Database\Builder\TableName;
use function Tempest\make;
use Tempest\Mapper\CastWith;
use function Tempest\type;

trait IsModel
{
    public ?Id $id = null;

    public function setId(Id $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public static function table(): TableName
    {
        return new TableName(pathinfo(str_replace('\\', '/', static::class), PATHINFO_FILENAME));
    }

    public static function field(string $field): FieldName
    {
        return new FieldName(
            tableName: self::table(),
            fieldName: $field,
        );
    }

    public static function relationField(string $relation): FieldName
    {
        $field = lcfirst(pathinfo(str_replace('\\', '/', $relation), PATHINFO_FILENAME)) . '_id';

        return self::field($field);
    }

    public static function new(...$params): self
    {
        return make(self::class)->from($params);
    }

    public static function all(): array
    {
        $table = self::table();

        $fields = self::fieldNames();

        $fields = implode(', ', array_map(
            fn (FieldName $fieldName) => $fieldName->asDefault(),
            $fields,
        ));

        return make(static::class)->collection()->from(new Query(
            "SELECT {$fields} FROM {$table}",
        ));
    }

    public static function find(Id $id, array $relations = []): self
    {
        $statements = [];

        $fields = self::fieldNames();

        /** @var class-string<\Tempest\Database\Model> $relation */
        foreach ($relations as $relation) {
            $fields = [...$fields, ...$relation::fieldNames()];
        }

        $fields = implode(', ', array_map(
            fn (FieldName $fieldName) => $fieldName->asDefault(),
            $fields,
        ));

        $statements[] = "SELECT {$fields} FROM " . self::table();

        foreach ($relations as $relation) {
            $statements[] = 'INNER JOIN ' . $relation::table() . ' ON ' . $relation::field('id') . ' = ' . self::relationField($relation);
        }

        $statements[] = 'WHERE ' . self::field('id') . ' = :id';
        $statements[] = 'LIMIT 1';

        $query = new Query(
            implode(PHP_EOL, $statements),
            ['id' => $id],
        );

        return make(static::class)->from($query);
    }

    public static function create(...$params): self
    {
        $model = self::new(...$params);

        $id = make(Query::class)->from($model)->execute();

        $model->setId($id);

        return $model;
    }

    public function save(): self
    {
        $id = make(Query::class)->from($this)->execute();

        $this->setId($id);

        return $this;
    }

    public function update(...$params): self
    {
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }

        $query = make(Query::class)->from($this);

        $query->execute();

        return $this;
    }

    public static function fieldNames(): array
    {
        $fieldNames = [];

        foreach ((new ReflectionClass(self::class))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (is_a(type($property), BackedEnum::class, true)) {
                $fieldNames[] = self::field($property->getName());

                continue;
            }

            if (! $property->getType()->isBuiltin()) {
                $castWith = attribute(CastWith::class)
                    ->in($property)
                    ->first();

                if (! $castWith) {
                    $castWith = attribute(CastWith::class)
                        ->in($property->getType()->getName())
                        ->first();
                }

                if ($castWith) {
                    $fieldNames[] = self::field($property->getName());
                }

                continue;
            }

            if ($property->getType()->getName() === 'array') {
                continue;
            }

            $fieldNames[] = self::field($property->getName());
        }

        return $fieldNames;
    }
}
