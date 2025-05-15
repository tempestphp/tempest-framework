<?php

namespace Tempest\Database;

use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Reflection\PropertyAttribute;
use Tempest\Support\Arr\ImmutableArray;

interface Relation extends PropertyAttribute
{
    public function getSelectFields(): ImmutableArray;

    public function getJoinStatement(): JoinStatement;
}
