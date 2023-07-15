<?php

declare(strict_types=1);

namespace Tempest\Interfaces;

use Tempest\ORM\Direction;
use Tempest\ORM\FieldName;
use Tempest\ORM\TableName;

/**
 * @template ModelClass
 */
interface Query
{
    public function select(string|FieldName ...$statement): self;

    public function from(TableName $table): self;

    public function join(TableName $table, FieldName $left, FieldName $right): self;

    public function where(FieldName $field, mixed $value): self;

    public function orderBy(FieldName $field, Direction $direction = Direction::ASC): self;

    public function insert(...$params): string;

    /**
     * @return ModelClass[]
     */
    public function get(): array;

    /**
     * @return ModelClass|null
     */
    public function first(): ?Model;
}
