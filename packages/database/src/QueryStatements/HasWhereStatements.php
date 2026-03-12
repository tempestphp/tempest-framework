<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Support\Arr\ImmutableArray;

interface HasWhereStatements
{
    /** @var ImmutableArray */
    public ImmutableArray $where {
        get;
    }
}
