<?php

namespace Tempest\Database\Exceptions;

use Exception;

final class HasManyRelationCouldNotBeUpdated extends Exception
{
    public function __construct(string $modelName, string $relationName)
    {
        parent::__construct("Cannot update {$modelName}::\${$relationName} via an update query. Update the related model directly instead");
    }
}
