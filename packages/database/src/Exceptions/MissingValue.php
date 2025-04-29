<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;

final class MissingValue extends Exception
{
    public function __construct(object $model, string $field)
    {
        $modelClass = $model::class;

        parent::__construct("Could not access {$modelClass}::{$field}, it's not initialized");
    }
}
