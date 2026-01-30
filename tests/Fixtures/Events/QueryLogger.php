<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Events;

use Tempest\Database\QueryExecuted;
use Tempest\EventBus\EventHandler;

final class QueryLogger
{
    /** @var QueryExecuted[] */
    public static array $queries = [];

    #[EventHandler]
    public function onQueryExecuted(QueryExecuted $event): void
    {
        self::$queries[] = $event;
    }

    public static function reset(): void
    {
        self::$queries = [];
    }
}
