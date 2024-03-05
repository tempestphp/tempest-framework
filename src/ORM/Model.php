<?php

declare(strict_types=1);

namespace Tempest\ORM;

use Tempest\Database\Builder\FieldName;
use Tempest\Database\Builder\TableName;
use Tempest\Database\Id;

interface Model
{
    public static function table(): TableName;

    public static function field(string $field): FieldName;

    public static function relationField(string $relation): FieldName;

    /** @return \Tempest\Database\Builder\FieldName[] */
    public static function fieldNames(): array;

    public static function all(): array;

    public static function new(...$params): self;

    public static function create(...$params): self;

    public static function find(Id $id): self;

    public function save(): self;

    public function getId(): Id;

    public function setId(Id $id): self;

    public function update(...$params): self;
}
