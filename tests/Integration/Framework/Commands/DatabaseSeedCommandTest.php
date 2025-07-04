<?php

namespace Tests\Tempest\Integration\Framework\Commands;

use Tempest\Core\AppConfig;
use Tempest\Core\Environment;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class DatabaseSeedCommandTest extends FrameworkIntegrationTestCase
{
    public function test_seed(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateBookTable::class,
        );

        $this->console
            ->call('db:seed')
            ->assertSuccess();

        $book = Book::get(1);

        $this->assertSame('Timeline Taxi', $book->title);
    }

    public function test_seed_via_migrate_fresh(): void
    {
        $this->console
            ->call('migrate:fresh --seed')
            ->assertSuccess();

        $book = Book::get(1);

        $this->assertSame('Timeline Taxi', $book->title);
    }

    public function test_db_seed_caution(): void
    {
        $appConfig = $this->container->get(AppConfig::class);
        $appConfig->environment = Environment::PRODUCTION;

        $this->console
            ->call('migrate:fresh --seed')
            ->assertSee('Do you wish to continue');
    }
}