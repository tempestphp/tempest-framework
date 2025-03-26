<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Query;

/**
 * @template TModelClass of object
 */
final readonly class DeleteModelQueryBuilder
{
    public function __construct(
        private object $model,
    ) {}

    public function build(): Query
    {
        $table = new ModelDefinition($this->model)->getTableDefinition();

        return new Query(
            sprintf(
                'DELETE FROM %s WHERE `id` = :id',
                $table,
            ),
            [
                'id' => $this->model->id->id,
            ],
        );
    }
}
