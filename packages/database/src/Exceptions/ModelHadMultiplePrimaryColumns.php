<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;

use function Tempest\Support\arr;

final class ModelHadMultiplePrimaryColumns extends Exception
{
    public static function found(string|object $model, array $properties): self
    {
        if (is_object($model)) {
            $model = get_class($model);
        }

        $propertyNames = arr($properties)->join();

        return new self("`{$model}` has multiple `Id` properties ({$propertyNames}). Only one `Id` property is allowed per model.");
    }
}
