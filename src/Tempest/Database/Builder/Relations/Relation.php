<?php

declare(strict_types=1);

namespace Tempest\Database\Builder\Relations;

interface Relation
{
    public function getRelationName(): string;

    public function getStatement(): string;

    /** @return \Tempest\Database\Builder\FieldName[] */
    public function getFieldNames(): array;
}
