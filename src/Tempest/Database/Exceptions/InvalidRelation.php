<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;
use Throwable;

final class InvalidRelation extends Exception
{
    public function __construct(string $modelClass, string $relationName, string $relationPart, Throwable $previous)
    {
        parent::__construct("Could not determine the relation '{$relationName}' ({$relationPart}) of {$modelClass}.", previous: $previous);
    }
}
