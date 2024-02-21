<?php

namespace Tempest\Interface;

use Tempest\Container\InitializedBy;
use Tempest\Database\DatabaseInitializer;
use Tempest\Database\Id;
use Tempest\Database\Query;

#[InitializedBy(DatabaseInitializer::class)]
interface Database
{
    public function execute(Query $query): void;

    public function getLastInsertId(): Id;

    public function fetch(Query $query): array;

    public function fetchFirst(Query $query): ?array;
}