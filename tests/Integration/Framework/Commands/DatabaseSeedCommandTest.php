<?php

namespace Tests\Tempest\Integration\Framework\Commands;

use Tempest\Core\AppConfig;
use Tempest\Core\Environment;
use Tempest\Database\Config\SeederConfig;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\SecondTestDatabaseSeeder;
use Tests\Tempest\Fixtures\TestDatabaseSeeder;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class DatabaseSeedCommandTest extends FrameworkIntegrationTestCase
{
    public function test_seed_with_selected_seeder(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateBookTable::class,
        );

        $this->console
            ->call('db:seed')
            ->assertSee(TestDatabaseSeeder::class)
            ->assertSee(SecondTestDatabaseSeeder::class)
            ->submit('1')
            ->submit()
            ->assertSuccess();

        $book = Book::get(1);
        $this->assertSame('Timeline Taxi 2', $book->title);
        $this->assertSame(1, query(Book::class)->count()->execute());
    }

    public function test_seed_with_manually_selected_seeder(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateBookTable::class,
        );

        $this->console
            ->call(sprintf('db:seed --seeder=%s', SecondTestDatabaseSeeder::class))
            ->assertSuccess();

        $book = Book::get(1);
        $this->assertSame('Timeline Taxi 2', $book->title);
        $this->assertSame(1, query(Book::class)->count()->execute());
    }

    public function test_migrate_fresh_seed_with_manually_selected_seeder(): void
    {
        $this->console
            ->call(sprintf('migrate:fresh --seeder=%s', SecondTestDatabaseSeeder::class))
            ->assertSuccess();

        $book = Book::get(1);
        $this->assertSame('Timeline Taxi 2', $book->title);
        $this->assertSame(1, query(Book::class)->count()->execute());
    }

    public function test_seed_all(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateBookTable::class,
        );

        $this->console
            ->call('db:seed --all')
            ->assertSuccess();

        $book = Book::get(1);
        $this->assertSame('Timeline Taxi', $book->title);

        $book = Book::get(2);
        $this->assertSame('Timeline Taxi 2', $book->title);

        $this->assertSame(2, query(Book::class)->count()->execute());
    }

    public function test_seed_when_only_one_seeder_is_available(): void
    {
        $seederConfig = $this->container->get(SeederConfig::class);
        unset($seederConfig->seeders[1]);

        $this->migrate(
            CreateMigrationsTable::class,
            CreateBookTable::class,
        );

        $this->console
            ->call('db:seed')
            ->assertSuccess();

        $book = Book::get(1);
        $this->assertSame('Timeline Taxi', $book->title);
        $this->assertSame(1, query(Book::class)->count()->execute());
    }

    public function test_seed_via_migrate_fresh(): void
    {
        $this->console
            ->call('migrate:fresh --seed --all')
            ->assertSuccess();

        $book = Book::get(1);

        $this->assertSame('Timeline Taxi', $book->title);
    }

    public function test_db_seed_caution(): void
    {
        $appConfig = $this->container->get(AppConfig::class);
        $appConfig->environment = Environment::PRODUCTION;

        $this->console
            ->call('migrate:fresh --seed --all')
            ->assertSee('Do you wish to continue');
    }
}
