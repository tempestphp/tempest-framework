<?php

namespace Tempest\Database;

use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Database\QueryStatements\WhereExistsStatement;
use Tempest\Reflection\PropertyAttribute;
use Tempest\Support\Arr\ImmutableArray;
use UnitEnum;

interface Relation extends PropertyAttribute
{
    public string $name { get; }

    public bool $withPropertyNameAlias { get; }

    public function setParent(string $name): self;

    public function withPropertyNameAlias(): self;

    public function getSelectFields(): ImmutableArray;

    public function getJoinStatement(): JoinStatement;

    public function getExistsStatement(): WhereExistsStatement;

    public function query(PrimaryKey $primaryKey, string|UnitEnum|null $onDatabase = null): QueryBuilder;
}
