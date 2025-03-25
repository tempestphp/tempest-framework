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

    /**
     * @template ModelType
     * @param ModelType $model
     * @return ModelType
     */
    public static function create(object $model): self
    {
        // TODO
    }

    /**
     * @template ModelType
     * @param ModelType $model
     * @return ModelType
     */
    public static function update(object $model): self
    {
        // TODO
    }

    public static function delete(object $model): self
    {
        // TODO
    }
}