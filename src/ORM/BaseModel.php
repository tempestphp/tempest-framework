<?php

declare(strict_types=1);

namespace Tempest\ORM;

use Tempest\Database\Builder\FieldName;
use Tempest\Database\Builder\TableName;
use Tempest\Database\Id;
use Tempest\Database\Query;
use Tempest\ORM\Attributes\Lazy;

trait BaseModel
{
    #[Lazy] public Id $id;

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

    public static function find(Id $id): self
    {
        $table = self::table();

        return make(static::class)->from(new Query(
            "SELECT * FROM {$table} WHERE id = :id LIMIT 1",
            ['id' => $id],
        ));
    }

    public static function create(...$params): self
    {
        $model = self::new(...$params);

        $id = make(Query::class)->from($model)->execute();

        $model->setId($id);

        return $model;
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
