<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Builder\SelectModelQuery;
use Tempest\Database\Builder\TableDefinition;

interface DatabaseModel
{
    /** @return \Tempest\Database\Builder\SelectModelQuery<self> */
    public static function query(): SelectModelQuery;

    /** @return self[] */
    public static function all(): array;

    public static function new(mixed ...$params): self;

    public static function create(mixed ...$params): self;

    public static function updateOrCreate(array $find, array $update): self;

    public static function get(Id $id, array $relations = []): ?self;

    public static function find(mixed ...$conditions): SelectModelQuery;

    public function save(): self;

    public function getId(): Id;

    public function setId(Id $id): self;

    public function update(mixed ...$params): self;

    public function load(string ...$relations): self;
}
