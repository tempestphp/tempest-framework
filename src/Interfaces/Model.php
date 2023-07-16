<?php

declare(strict_types=1);

namespace Tempest\Interfaces;

use Tempest\Database\Builder\FieldName;
use Tempest\Database\Builder\TableName;
use Tempest\Database\Id;

interface Model
{
    public static function table(): TableName;

    public static function field(string $field): FieldName;

    public static function all(): array;

    public static function create(...$params): self;

    public static function find(Id $id): self;

    public function getId(): Id;

    public function setId(Id $id): self;

    public function update(...$params): self;
}
