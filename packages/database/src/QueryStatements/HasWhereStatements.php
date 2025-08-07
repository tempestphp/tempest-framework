<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Support\Arr\ImmutableArray;

interface HasWhereStatements
{
    /** @var ImmutableArray<WhereStatement> */
    public ImmutableArray $where {
        get;
    }
}
