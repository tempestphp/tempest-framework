<?php

namespace Tempest\Database\Exceptions;

use Exception;

final class CannotUpdateHasManyRelation extends Exception
{
    public function __construct(string $modelName, string $relationName)
    {
        parent::__construct("Cannot update {$modelName}::\${$relationName} via an update query. Attach the related has many model manually instead");
    }
}
