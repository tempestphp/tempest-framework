<?php

namespace Tempest\Database {
    use Tempest\Database\Builder\ModelInspector;
    use Tempest\Database\Builder\ModelQueryBuilder;
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
     * Provides model-related convenient query methods.
     *
     * @template TModel of object
     * @param class-string<TModel> $modelClass
     * @return ModelQueryBuilder<TModel>
     */
    function model(string $modelClass): ModelQueryBuilder
    {
        return new ModelQueryBuilder($modelClass);
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
        return new ModelInspector($model);
    }
}
