<?php

declare(strict_types=1);

namespace Tempest\Database;

interface Database
{
    public function execute(Query $query): void;

    public function getLastInsertId(): Id;

    public function fetch(Query $query): array;

    public function fetchFirst(Query $query): ?array;
}
