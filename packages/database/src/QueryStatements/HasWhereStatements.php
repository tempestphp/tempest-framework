<?php

namespace Tempest\Database\QueryStatements;

use Tempest\Support\Arr\ImmutableArray;

interface HasWhereStatements
{
    public ImmutableArray $where {
        get;
    }
}