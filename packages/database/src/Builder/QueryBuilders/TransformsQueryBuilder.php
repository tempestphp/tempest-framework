<?php

namespace Tempest\Database\Builder\QueryBuilders;

use function Tempest\Support\tap;

/**
 * @phpstan-require-implements BuildsQuery
 */
trait TransformsQueryBuilder
{
    /**
     * Returns a new instance of the query builder with the given callback applied.
     * @param callable(static): mixed $callback
     */
    public function transform(callable $callback): static
    {
        return tap(clone $this, $callback);
    }
}
