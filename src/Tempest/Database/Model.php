<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\FieldName;
use Tempest\Database\Builder\ModelQueryBuilder;
use Tempest\Database\Builder\TableName;

interface Model
{
    // TODO: refactor all these field methods to ModelQueryBuilder
    public static function table(): TableName;

    public static function field(string $field): FieldName;

    public static function relationField(string $relation): FieldName;

    /**
     * @return \Tempest\Database\Builder\ModelQueryBuilder<self>
     */
    public static function query(): ModelQueryBuilder;

    public static function all(): array;

    public static function new(...$params): self;

    public static function create(...$params): self;

    public static function find(Id $id): self;

    public function save(): self;

    public function getId(): Id;

    public function setId(Id $id): self;

    public function update(...$params): self;
}
