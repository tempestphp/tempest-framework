<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Mapper\SerializerFactory;

use function Tempest\get;
use function Tempest\Support\arr;

/**
 * @template T of object
 */
final readonly class QueryBuilder
{
    /**
     * @param class-string<T>|string|T $model
     */
    public function __construct(
        private string|object $model,
    ) {}

    /**
     * @return SelectQueryBuilder<T>
     */
    public function select(string ...$columns): SelectQueryBuilder
    {
        return new SelectQueryBuilder(
            model: $this->model,
            fields: $columns !== [] ? arr($columns) : null,
        );
    }

    /**
     * @return InsertQueryBuilder<T>
     */
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

    /**
     * @return UpdateQueryBuilder<T>
     */
    public function update(mixed ...$values): UpdateQueryBuilder
    {
        return new UpdateQueryBuilder(
            model: $this->model,
            values: $values,
            serializerFactory: get(SerializerFactory::class),
        );
    }

    /**
     * @return DeleteQueryBuilder<T>
     */
    public function delete(): DeleteQueryBuilder
    {
        return new DeleteQueryBuilder($this->model);
    }

    /**
     * @return CountQueryBuilder<T>
     */
    public function count(?string $column = null): CountQueryBuilder
    {
        return new CountQueryBuilder(
            model: $this->model,
            column: $column,
        );
    }
}
