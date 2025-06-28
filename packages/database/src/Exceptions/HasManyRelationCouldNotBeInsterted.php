<?php

namespace Tempest\Database\Exceptions;

use Exception;

final class HasManyRelationCouldNotBeInsterted extends Exception
{
    public function __construct(string $modelName, string $relationName)
    {
        parent::__construct("Cannot create {$modelName}::\${$relationName} via an insert query. Attach the related has many model manually instead");
    }
}
