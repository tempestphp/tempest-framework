<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Mapper\SerializerFactory;

use function Tempest\get;
use function Tempest\Support\arr;

/**
 * @template TModel of object
 */
final readonly class QueryBuilder
{
    /**
     * @param class-string<TModel>|string|TModel $model
     */
    public function __construct(
        private string|object $model,
    ) {}

    /**
     * Creates a `SELECT` query builder for retrieving records from the database.
     *
     * @return SelectQueryBuilder<TModel>
     */
    public function select(string ...$columns): SelectQueryBuilder
    {
        return new SelectQueryBuilder(
            model: $this->model,
            fields: $columns !== [] ? arr($columns) : null,
        );
    }

    /**
     * Creates an `INSERT` query builder for adding new records to the database.
     *
     * @return InsertQueryBuilder<TModel>
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
     * Creates an `UPDATE` query builder for modifying existing records in the database.
     *
     * @return UpdateQueryBuilder<TModel>
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
     * Creates a `DELETE` query builder for removing records from the database.
     *
     * @return DeleteQueryBuilder<TModel>
     */
    public function delete(): DeleteQueryBuilder
    {
        return new DeleteQueryBuilder($this->model);
    }

    /**
     * Creates a `COUNT` query builder for counting records in the database.
     *
     * @return CountQueryBuilder<TModel>
     */
    public function count(?string $column = null): CountQueryBuilder
    {
        return new CountQueryBuilder(
            model: $this->model,
            column: $column,
        );
    }
}
