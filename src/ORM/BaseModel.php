<?php

declare(strict_types=1);

namespace Tempest\ORM;

use Closure;
use Tempest\Interfaces\Query;

trait BaseModel
{
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

    /**
     * @return Query<static>
     */
    public static function query(): Query
    {
        return ModelQuery::new(static::class);
    }

    public static function create(...$params): static
    {
        $id = self::query()->insert(...$params);

        return self::query()
            ->where(static::field('id'), $id)
            ->first();
    }

    public function load(Closure $load): self
    {
        return $this;
    }
}
