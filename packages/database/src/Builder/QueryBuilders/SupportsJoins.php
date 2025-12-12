<?php

namespace Tempest\Database\Builder\QueryBuilders;

use Tempest\Database\QueryStatements\JoinStatement;

/**
 * @template TModel of object
 */
interface SupportsJoins
{
    /**
     * The current JOIN statements for this query builder.
     *
     * @return array<JoinStatement|string>
     */
    public array $joins {
        get;
    }
}
