<?php

declare(strict_types=1);

namespace Tempest\ORM;

use Tempest\Database\Builder\FieldName;
use Tempest\Database\Builder\TableName;
use Tempest\Database\Id;
use Tempest\Database\Query;

trait BaseModel
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

        return make(static::class)->collection()->from(new Query(
            "SELECT * FROM {$table}",
        ));
    }

    public static function find(Id $id, array $relations = []): self
    {
        $statements = [];

        $statements[] = 'SELECT * FROM ' . self::table();

        /** @var class-string<\Tempest\Interfaces\Model> $relation
         */
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
}
