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

    public static function find(Id $id): self
    {
        $table = self::table();

        $params = (new Query(
            "SELECT * FROM {$table} WHERE id = :id LIMIT 1",
            ['id' => $id],
        ))->fetchFirst();

        $model = make(static::class)->from($params);

        return $model;
    }

    public static function create(...$params): self
    {
        $columns = implode(', ', array_keys($params));

        $valuePlaceholders = implode(', ', array_map(
            fn (string $key) => ":{$key}",
            array_keys($params),
        ));

        $table = self::table();

        $id = (new Query(
            "INSERT INTO {$table} ({$columns}) VALUES ({$valuePlaceholders})",
            $params,
        ))->execute();

        $model = make(static::class)->from($params);

        $model->setId($id);

        return $model;
    }

    public function update(...$params): self
    {
        $values = implode(', ', array_map(
            fn (string $key) => "{$key} = :{$key}",
            array_keys($params),
        ));

        $table = self::table();

        (new Query(
            "UPDATE {$table} SET {$values} WHERE id = {$this->id}",
            $params,
        ))->execute();

        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }

        return self::find($this->id);
    }
}
