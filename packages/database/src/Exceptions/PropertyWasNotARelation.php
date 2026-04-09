<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;

final class PropertyWasNotARelation extends Exception
{
    public function __construct(
        public readonly string $property,
        public readonly string $model,
    ) {
        parent::__construct("Property `{$property}` is not a relation on `{$model}`.");
    }
}
