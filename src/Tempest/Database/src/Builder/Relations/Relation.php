<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\Relations;

use Tempest\Support\Arr\ImmutableArray;

interface Relation
{
    public function getRelationName(): string;

    public function getStatement(): string;

    /** @return ImmutableArray<\Tempest\Database\Builder\FieldName> */
    public function getFieldNames(): ImmutableArray;
}
