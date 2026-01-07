<?php

namespace Tempest\Database {
    use Tempest\Database\Builder\ModelInspector;
    use Tempest\Database\Builder\QueryBuilders\QueryBuilder;

    /**
     * Creates a new query builder instance for the given model or table name.
     *
     * @template TModel of object
     * @param class-string<TModel>|string|TModel $model
     * @return QueryBuilder<TModel>
     */
    function query(string|object $model): QueryBuilder
    {
        return new QueryBuilder($model);
    }

    /**
     * Inspects the given model or table name to provide database insights.
     *
     * @template TModel of object
     * @param class-string<TModel>|string|TModel $model
     * @return ModelInspector<TModel>
     * @internal
     */
    function inspect(string|object $model): ModelInspector
    {
        return ModelInspector::forModel($model);
    }
}
