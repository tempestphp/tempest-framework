<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;
use Tempest\Database\DatabaseModel;

final class MissingValue extends Exception
{
    public function __construct(DatabaseModel $model, string $field)
    {
        $modelClass = $model::class;

        parent::__construct("Could not access {$modelClass}::{$field}, it's not initialized");
    }
}
