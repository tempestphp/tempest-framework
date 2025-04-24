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

    public function insert(mixed ...$values): InsertQueryBuilder
    {
        if (! array_is_list($values)) {
            $values = [$values];
        }

        return new InsertQueryBuilder(
            model: $this->model,
            rows: $values,
            serializerFactory: get(SerializerFactory::class),
        );
    }

    public function update(mixed ...$values): UpdateQueryBuilder
    {
        return new UpdateQueryBuilder(
            model: $this->model,
            values: $values,
            serializerFactory: get(SerializerFactory::class),
        );
    }

    public function delete(): DeleteQueryBuilder
    {
        return new DeleteQueryBuilder($this->model);
    }

    public function count(?string $column = null): CountQueryBuilder
    {
        return new CountQueryBuilder(
            model: $this->model,
            column: $column,
        );
    }
}
