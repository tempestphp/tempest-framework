<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\Relations;

use Tempest\Support\Arr\ImmutableArray;

// TODO: remove
interface Relation
{
    public function getRelationName(): string;

    public function getStatement(): string;

    /** @return ImmutableArray<\Tempest\Database\Builder\FieldDefinition> */
    public function getFieldDefinitions(): ImmutableArray;
}
