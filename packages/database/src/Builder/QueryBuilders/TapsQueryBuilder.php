<?php

namespace Tempest\Database\Builder\QueryBuilders;

use function Tempest\Support\tap;

/**
 * @phpstan-require-implements BuildsQuery
 */
trait TapsQueryBuilder
{
    /**
     * Returns a new instance of the query builder with the given callback applied.
     *
     * @param callable($this) $callback
     */
    public function tap(callable $callback): static
    {
        return tap($this->clone(), $callback);
    }
}
