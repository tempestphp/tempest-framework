<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tempest\Clock\MockClock;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\DateTime\DateTime;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class ConvenientDateWhereMethodsTest extends FrameworkIntegrationTestCase
{
    private MockClock $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = $this->clock('2025-08-02 12:00:00');

        $this->database->migrate(CreateMigrationsTable::class, CreateEventTable::class);
        $this->seedTestData();
    }

    private function seedTestData(): void
    {
        $now = $this->clock->now();

        query(Event::class)
            ->insert(
                name: 'Today event 1',
                created_at: $now->withTime(10, 0),
                event_date: $now->withTime(14, 0),
            )
            ->execute();

        query(Event::class)
            ->insert(
                name: 'Today event 2',
                created_at: $now->withTime(11, 0),
                event_date: $now->withTime(16, 0),
            )
            ->execute();

        $yesterday = $now->minusDay();
        query(Event::class)
            ->insert(
                name: 'Yesterday event',
                created_at: $yesterday->withTime(9, 0),
                event_date: $yesterday->withTime(13, 0),
            )
            ->execute();

        $thisWeekSunday = DateTime::parse('2025-08-03 17:00:00');
        query(Event::class)
            ->insert(
                name: 'This week Sunday event',
                created_at: $thisWeekSunday->withTime(8, 0),
                event_date: $thisWeekSunday,
            )
            ->execute();

        $lastWeekTuesday = DateTime::parse('2025-07-22 15:00:00');
        query(Event::class)
            ->insert(
                name: 'Last week event 1',
                created_at: $lastWeekTuesday->withTime(10, 0),
                event_date: $lastWeekTuesday,
            )
            ->execute();

        $lastWeekWednesday = DateTime::parse('2025-07-23 16:00:00');
        query(Event::class)
            ->insert(
                name: 'Last week event 2',
                created_at: $lastWeekWednesday->withTime(11, 0),
                event_date: $lastWeekWednesday,
            )
            ->execute();

        $thisMonthDay = DateTime::parse('2025-08-10 10:00:00');
        query(Event::class)
            ->insert(
                name: 'This month event',
                created_at: $thisMonthDay,
                event_date: $thisMonthDay->withTime(14, 0),
            )
            ->execute();

        $lastMonth = DateTime::parse('2025-07-15 10:00:00');
        query(Event::class)
            ->insert(
                name: 'Last month event',
                created_at: $lastMonth,
                event_date: $lastMonth->withTime(16, 0),
            )
            ->execute();

        $thisYearDay = DateTime::parse('2025-03-15 10:00:00');
        query(Event::class)
            ->insert(
                name: 'This year event',
                created_at: $thisYearDay,
                event_date: $thisYearDay->withTime(11, 0),
            )
            ->execute();

        $lastYear = DateTime::parse('2024-08-02 10:00:00');
        query(Event::class)
            ->insert(
                name: 'Last year event',
                created_at: $lastYear,
                event_date: $lastYear->withTime(14, 0),
            )
            ->execute();

        $future = $now->plusDays(30);
        query(Event::class)
            ->insert(
                name: 'Future event',
                created_at: $now,
                event_date: $future->withTime(10, 0),
            )
            ->execute();
    }

    public function test_where_today(): void
    {
        $events = query(Event::class)
            ->select()
            ->whereToday('event_date')
            ->all();

        $this->assertCount(2, $events);

        foreach ($events as $event) {
            $this->assertStringContainsString('Today event', $event->name);
            $this->assertTrue($event->event_date->isToday());
        }
    }

    public function test_where_yesterday(): void
    {
        $events = query(Event::class)
            ->select()
            ->whereYesterday('event_date')
            ->all();

        $this->assertCount(1, $events);
        $this->assertSame('Yesterday event', $events[0]->name);

        $this->assertTrue($events[0]->event_date->isYesterday());
    }

    public function test_where_this_week(): void
    {
        $events = query(Event::class)
            ->select()
            ->whereThisWeek('event_date')
            ->all();

        $this->assertCount(4, $events);

        foreach ($events as $event) {
            $this->assertTrue($event->event_date->isCurrentWeek());
        }
    }

    public function test_where_last_week(): void
    {
        $events = query(Event::class)->select()->whereLastWeek('event_date')->all();

        $this->assertCount(2, $events);
        foreach ($events as $event) {
            $this->assertStringContainsString('Last week event', $event->name);
        }
    }

    public function test_where_this_month(): void
    {
        $events = query(Event::class)
            ->select()
            ->whereThisMonth('event_date')
            ->all();

        $this->assertCount(5, $events);

        foreach ($events as $event) {
            $this->assertTrue($event->event_date->isCurrentMonth());
        }
    }

    public function test_where_last_month(): void
    {
        $events = query(Event::class)
            ->select()
            ->whereLastMonth('event_date')
            ->all();

        $this->assertCount(3, $events);

        $names = array_column($events, 'name');
        $this->assertContains('Last month event', $names);
        $this->assertContains('Last week event 1', $names);
        $this->assertContains('Last week event 2', $names);

        foreach ($events as $event) {
            $this->assertTrue($event->event_date->isPreviousMonth());
        }
    }

    public function test_where_this_year(): void
    {
        $events = query(Event::class)->select()->whereThisYear('event_date')->all();

        $this->assertCount(10, $events);

        foreach ($events as $event) {
            if ($event->name !== 'Last year event') {
                $this->assertTrue($event->event_date->isCurrentYear());
            }
        }
    }

    public function test_where_last_year(): void
    {
        $events = query(Event::class)->select()->whereLastYear('event_date')->all();

        $this->assertCount(1, $events);
        $this->assertSame('Last year event', $events[0]->name);
        $this->assertTrue($events[0]->event_date->isPreviousYear());
    }

    public function test_where_after(): void
    {
        $cutoffDate = $this->clock->now()->plusDays(15);
        $events = query(Event::class)
            ->select()
            ->whereAfter('event_date', $cutoffDate)
            ->all();

        $this->assertCount(1, $events);
        $this->assertSame('Future event', $events[0]->name);

        $this->assertTrue($events[0]->event_date->isAfter($cutoffDate));
    }

    public function test_where_before(): void
    {
        $cutoffDate = $this->clock->now()->minusDays(15);

        $events = query(Event::class)
            ->select()
            ->whereBefore('event_date', $cutoffDate)
            ->all();

        $this->assertGreaterThanOrEqual(1, count($events));

        /** @var Event $event */
        foreach ($events as $event) {
            $this->assertTrue($event->event_date->isBefore($cutoffDate));
        }
    }

    public function test_where_between_with_datetime(): void
    {
        $start = $this->clock->now()->minusDays(2);
        $end = $this->clock->now()->plusDays(1);

        $events = query(Event::class)
            ->select()
            ->whereBetween('event_date', $start, $end)
            ->all();

        // Today and yesterday events
        $this->assertCount(3, $events);

        /** @var Event $event */
        foreach ($events as $event) {
            $this->assertTrue($event->event_date->betweenTimeInclusive($start, $end));
        }
    }

    public function test_or_where_today(): void
    {
        $events = query(Event::class)
            ->select()
            ->whereLastYear('event_date')
            ->orWhereToday('event_date')
            ->all();

        $this->assertCount(3, $events);

        $hasLastYear = false;
        $todayEventsCount = 0;

        foreach ($events as $event) {
            if ($event->name === 'Last year event') {
                $hasLastYear = true;
            } elseif (str_contains($event->name, 'Today event')) {
                $todayEventsCount++;
            }
        }

        $this->assertTrue($hasLastYear);
        $this->assertSame(2, $todayEventsCount);
    }

    public function test_or_where_yesterday(): void
    {
        $events = query(Event::class)
            ->select()
            ->whereLastMonth('event_date')
            ->orWhereYesterday('event_date')
            ->all();

        $this->assertCount(4, $events);

        $names = array_column($events, 'name');
        $this->assertContains('Last month event', $names);
        $this->assertContains('Last week event 1', $names);
        $this->assertContains('Last week event 2', $names);
        $this->assertContains('Yesterday event', $names);
    }

    public function test_complex_date_query_combination(): void
    {
        $events = query(Event::class)
            ->select()
            ->whereThisWeek('event_date')
            ->orWhereThisMonth('created_at')
            ->all();

        $this->assertGreaterThanOrEqual(4, count($events));
    }

    public function test_chaining_date_methods(): void
    {
        $events = query(Event::class)
            ->select()
            ->whereToday('event_date')
            ->whereToday('created_at')
            ->all();

        $this->assertCount(2, $events);

        foreach ($events as $event) {
            $this->assertTrue($event->event_date->isToday());
            $this->assertTrue($event->created_at->isToday());
        }
    }

    public function test_where_methods_with_string_dates(): void
    {
        $stringDate = '2025-08-02';
        $events = query(Event::class)
            ->select()
            ->whereAfter('event_date', $stringDate)
            ->all();

        $this->assertGreaterThanOrEqual(1, count($events));
    }

    public function test_edge_case_month_boundary(): void
    {
        $this->clock('2025-07-31 23:59:59');

        $events = query(Event::class)
            ->select()
            ->whereToday('created_at')
            ->all();

        $this->assertCount(0, $events);
    }
}

final class CreateEventTable implements MigratesUp
{
    private(set) string $name = '0000-00-10_create_events_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Event::class)
            ->primary()
            ->text('name')
            ->datetime('created_at')
            ->datetime('event_date');
    }
}

final class Event
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
        public DateTime $created_at,
        public DateTime $event_date,
    ) {}
}
