<?php

namespace Tempest\Database {
    use Tempest\Database\Builder\ModelInspector;
    use Tempest\Database\Builder\QueryBuilders\QueryBuilder;

    /**
     * @template T of object
     * @param class-string<T>|string|T $model
     * @return QueryBuilder<T>
     */
    function query(string|object $model): QueryBuilder
    {
        return new QueryBuilder($model);
    }

    /**
     * @template T of object
     * @param class-string<T>|string|T $model
     * @return ModelInspector<T>
     */
    function inspect(string|object $model): ModelInspector
    {
        return new ModelInspector($model);
    }
}
