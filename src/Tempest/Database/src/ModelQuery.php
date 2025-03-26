<?php

namespace Tempest\Database;

use Tempest\Database\Builder\SelectModelQuery;

final class ModelQuery
{
    /** @param class-string $modelClass */
    public static function select(string $modelClass): SelectModelQuery
    {
        return new SelectModelQuery($modelClass);
    }

    public static function create(object $model): object
    {
        // TODO
    }

    public static function update(object $model): object
    {
        // TODO
    }

    public static function delete(object $model): object
    {
        // TODO
    }
}
