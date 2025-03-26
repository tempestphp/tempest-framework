<?php

namespace Tempest\Database\Builder\Queries;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Query;

/**
 * @template TModelClass of object
 */
final readonly class DeleteModelQuery
{
    public function build(object $model): Query
    {
        $table = new ModelDefinition($model)->getTableDefinition();

        return new Query(
            sprintf(
                'DELETE FROM %s WHERE `id` = :id',
                $table,
            ),
            [
                'id' => $model->id->id,
            ],
        );
    }
}
