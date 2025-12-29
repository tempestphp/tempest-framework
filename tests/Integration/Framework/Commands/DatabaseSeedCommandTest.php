<?php

namespace Tests\Tempest\Integration\Framework\Commands;

use Tempest\Core\Environment;
use Tempest\Database\Config\SeederConfig;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
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
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $this->console
            ->call('db:seed')
            ->assertSee(TestDatabaseSeeder::class)
            ->assertSee(SecondTestDatabaseSeeder::class)
            ->submit('1')
            ->submit()
            ->assertSuccess();

        $this->assertSame(1, query(Book::class)->count()->execute());
    }

    public function test_seed_with_manually_selected_seeder(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $this->console
            ->call(sprintf('db:seed --seeder=%s', SecondTestDatabaseSeeder::class))
            ->assertSuccess();

        $this->assertSame(1, query(Book::class)->count()->execute());
        $book = Book::get(1);
        $this->assertSame('Timeline Taxi 2', $book->title);
    }

    public function test_migrate_fresh_seed_with_manually_selected_seeder(): void
    {
        $this->console
            ->call(sprintf('migrate:fresh --seeder=%s', SecondTestDatabaseSeeder::class))
            ->assertSuccess();

        $this->assertSame(1, query(Book::class)->count()->execute());
        $book = Book::get(1);
        $this->assertSame('Timeline Taxi 2', $book->title);
    }

    public function test_seed_all(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $this->console
            ->call('db:seed --all')
            ->assertSuccess();

        $this->assertSame(2, query(Book::class)->count()->execute());

        $book = Book::select()->where('title', 'Timeline Taxi')->first();
        $this->assertNotNull($book);

        $book = Book::select()->where('title', 'Timeline Taxi 2')->first();
        $this->assertNotNull($book);
    }

    public function test_seed_when_only_one_seeder_is_available(): void
    {
        $seederConfig = $this->container->get(SeederConfig::class);
        $seederConfig->seeders = [
            TestDatabaseSeeder::class,
        ];

        $this->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $this->console
            ->call('db:seed')
            ->assertSuccess();

        $this->assertSame(1, query(Book::class)->count()->execute());
        $book = Book::get(1);
        $this->assertSame('Timeline Taxi', $book->title);
    }

    public function test_seed_via_migrate_fresh(): void
    {
        $this->console
            ->call('migrate:fresh --seed --all')
            ->assertSuccess();

        $this->assertSame(2, query(Book::class)->count()->execute());

        $book = Book::select()->whereField('title', 'Timeline Taxi')->first();
        $this->assertNotNull($book);

        $book = Book::select()->whereField('title', 'Timeline Taxi 2')->first();
        $this->assertNotNull($book);
    }

    public function test_db_seed_caution(): void
    {
        $this->container->singleton(Environment::class, Environment::PRODUCTION);

        $this->console
            ->call('migrate:fresh --seed --all')
            ->assertSee('Do you wish to continue');
    }
}
