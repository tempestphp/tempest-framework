<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Mapper\SerializerFactory;

use function Tempest\get;
use function Tempest\Support\arr;
use function Tempest\Support\Arr\is_list;

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

    public function insert(mixed ...$data): InsertQueryBuilder
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        return new InsertQueryBuilder(
            model: $this->model,
            rows: $data,
            serializerFactory: get(SerializerFactory::class),
        );
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
