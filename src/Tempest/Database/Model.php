<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\ModelQueryBuilder;
use Tempest\Database\Builder\TableName;

interface Model
{
    public static function table(): TableName;

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
