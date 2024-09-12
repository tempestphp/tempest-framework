<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;
use Tempest\Database\DatabaseModel;

final class MissingRelation extends Exception
{
    public function __construct(DatabaseModel $model, string $relation)
    {
        $modelClass = $model::class;

        parent::__construct("Could not access {$modelClass}::{$relation}, did you forget to load it?");
    }
}
