<?php

namespace Tempest\Framework\Testing;

/**
 * @template TModelClass
 * @param class-string<TModelClass> $modelClass
 * @return ModelFactory<TModelClass>
 */
function factory(string $modelClass): ModelFactory
{
    return new ModelFactory($modelClass);
}
