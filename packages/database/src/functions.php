<?php

namespace Tempest\Database {
    use Tempest\Database\Builder\ModelInspector;
    use Tempest\Database\Builder\ModelQueryBuilder;
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
}
