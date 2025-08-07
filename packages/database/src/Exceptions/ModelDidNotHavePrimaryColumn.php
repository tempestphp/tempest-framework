<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;

final class ModelDidNotHavePrimaryColumn extends Exception implements DatabaseException
{
    public static function neededForMethod(string|object $model, string $method): self
    {
        if (is_object($model)) {
            $model = get_class($model);
        }

        return new self("`{$model}` does not have a primary column defined, which is required for the `{$method}` method.");
    }

    public static function neededForRelation(string|object $model, string $relationType): self
    {
        if (is_object($model)) {
            $model = get_class($model);
        }

        return new self("`{$model}` does not have a primary column defined, which is required for `{$relationType}` relationships.");
    }
}
