<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;
use Tempest\Database\Builder\ModelInspector;

final class CouldNotUpdateRelation extends Exception implements DatabaseException
{
    public static function requiresSingleRecord(ModelInspector $model): self
    {
        return new self(sprintf(
            'Attempted to update a relation on %s without targeting a single record by primary key. Use `where(%s, $id)` to update relations.',
            $model->getName(),
            $model->getPrimaryKey(),
        ));
    }

    public static function requiresPrimaryKey(ModelInspector $model): self
    {
        return new self(sprintf('Attempted to update a relation on %s, but it does not have a primary key.', $model->getName()));
    }
}
