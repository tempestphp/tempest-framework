<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryExecuted;
use Tempest\EventBus\EventBus;
use Tests\Tempest\Fixtures\Events\QueryLogger;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class QueryExecutedTest extends FrameworkIntegrationTestCase
{
    /** @var QueryExecuted[] */
    private array $dispatched = [];

    private function listenForQueryEvents(): void
    {
        $this->container->get(EventBus::class)->listen(function (QueryExecuted $event): void {
            $this->dispatched[] = $event;
        });
    }

    #[Test]
    public function query_executed_event_is_dispatched_on_insert(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $this->listenForQueryEvents();

        Book::new(title: 'Timeline Taxi')->save();

        $this->assertNotEmpty($this->dispatched);
        $this->assertFalse($this->dispatched[0]->failed);
        $this->assertStringContainsString('INSERT', $this->dispatched[0]->sql);
    }

    #[Test]
    public function query_executed_event_is_dispatched_on_select(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'Timeline Taxi')->save();

        $this->listenForQueryEvents();

        $books = Book::select()->all();

        $this->assertCount(1, $books);
        $this->assertNotEmpty($this->dispatched);

        $selectEvent = $this->dispatched[0];
        $this->assertFalse($selectEvent->failed);
        $this->assertStringContainsString('SELECT', $selectEvent->sql);
        $this->assertGreaterThanOrEqual(0.0, $selectEvent->durationMs);
    }

    #[Test]
    public function query_executed_event_is_dispatched_on_failure(): void
    {
        $this->listenForQueryEvents();

        try {
            Book::select()->orderByRaw('title DES')->first();
        } catch (QueryWasInvalid) { // @mago-expect lint:no-empty-catch-clause
        }

        $this->assertNotEmpty($this->dispatched);
        $this->assertTrue($this->dispatched[0]->failed);
    }

    #[Test]
    public function query_executed_event_has_timing_and_bindings(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'Timeline Taxi')->save();

        $this->listenForQueryEvents();

        Book::select()->where('title', 'Timeline Taxi')->all();

        $this->assertNotEmpty($this->dispatched);

        $event = $this->dispatched[0];
        $this->assertFalse($event->failed);
        $this->assertGreaterThanOrEqual(0.0, $event->durationMs);
        $this->assertNotEmpty($event->bindings);
    }

    #[Test]
    public function discovered_event_handler_receives_query_executed(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        QueryLogger::reset();

        Book::new(title: 'Timeline Taxi')->save();
        Book::select()->all();

        $this->assertNotEmpty(QueryLogger::$queries);

        $sqls = array_map(fn (QueryExecuted $e) => $e->sql, QueryLogger::$queries);
        $this->assertTrue(
            in_array(true, array_map(fn (string $sql) => str_contains($sql, 'INSERT'), $sqls), true),
        );
        $this->assertTrue(
            in_array(true, array_map(fn (string $sql) => str_contains($sql, 'SELECT'), $sqls), true),
        );
    }
}
