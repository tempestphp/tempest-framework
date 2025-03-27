<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Mapper\SerializerFactory;

use function Tempest\get;
use function Tempest\Support\arr;

final readonly class QueryBuilder
{
    public function __construct(
        private string|object $model,
    ) {}

    public function select(string ...$columns): SelectQueryBuilder
    {
        return new SelectQueryBuilder(
            model: $this->model,
            columns: $columns !== [] ? arr($columns) : null,
        );
    }

    public function create(): CreateQueryBuilder
    {
        return new CreateQueryBuilder($this->model, get(SerializerFactory::class));
    }

    public function update(): UpdateQueryBuilder
    {
        return new UpdateQueryBuilder($this->model, get(SerializerFactory::class));
    }

    public function delete(): DeleteQueryBuilder
    {
        return new DeleteQueryBuilder($this->model);
    }
}
