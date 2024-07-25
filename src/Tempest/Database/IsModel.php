<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\FieldName;
use Tempest\Database\Builder\ModelQueryBuilder;
use Tempest\Database\Builder\TableName;
use function Tempest\make;

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

    /**
     * @return \Tempest\Database\Builder\ModelQueryBuilder<self>
     */
    public static function query(): ModelQueryBuilder
    {
        return new ModelQueryBuilder(self::class);
    }

    public static function all(array $relations = []): array
    {
        return self::query()
            ->with(...$relations)
            ->all();
    }

    public static function find(Id $id, array $relations = []): self
    {
        /** @phpstan-ignore-next-line  */
        return self::query()
            ->with(...$relations)
            ->where(self::field('id') . ' = :id')
            ->first(id: $id);
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
