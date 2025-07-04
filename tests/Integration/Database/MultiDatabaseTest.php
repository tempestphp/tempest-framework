<?php

namespace Tests\Tempest\Integration\Database;

use PDOException;
use Tempest\Container\Exceptions\TaggedDependencyCouldNotBeResolved;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Config\MysqlConfig;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Database;
use Tempest\Database\DatabaseInitializer;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Migrations\Migration;
use Tempest\Database\Migrations\MigrationManager;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Publisher;
use Tests\Tempest\Integration\Database\Fixtures\MigrationForBackup;
use Tests\Tempest\Integration\Database\Fixtures\MigrationForMain;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\TestingDatabaseInitializer;

use function Tempest\Database\query;

/**
 * @property \Tempest\Console\Testing\ConsoleTester $console
 */
final class MultiDatabaseTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Multiple databases are not properly supported on Windows yet');
        }

        $files = [
            __DIR__ . '/Fixtures/main.sqlite',
            __DIR__ . '/Fixtures/backup.sqlite',
        ];

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }

            touch($file);
        }

        $this->container->removeInitializer(TestingDatabaseInitializer::class);
        $this->container->addInitializer(DatabaseInitializer::class);

        $this->container->config(new SQLiteConfig(
            path: __DIR__ . '/Fixtures/main.sqlite',
            tag: 'main',
        ));

        $this->container->config(new SQLiteConfig(
            path: __DIR__ . '/Fixtures/backup.sqlite',
            tag: 'backup',
        ));
    }

    public function test_with_multiple_connections(): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        $migrationManager->onDatabase('main')->executeUp(new CreateMigrationsTable());
        $migrationManager->onDatabase('main')->executeUp(new CreatePublishersTable());
        $migrationManager->onDatabase('backup')->executeUp(new CreateMigrationsTable());
        $migrationManager->onDatabase('backup')->executeUp(new CreatePublishersTable());

        query(Publisher::class)
            ->insert(
                id: new Id(1),
                name: 'Main 1',
                description: 'Description Main 1',
            )
            ->onDatabase('main')
            ->execute();

        query(Publisher::class)
            ->insert(
                id: new Id(2),
                name: 'Main 2',
                description: 'Description Main 2',
            )
            ->onDatabase('main')
            ->execute();

        query(Publisher::class)
            ->insert(
                id: new Id(1),
                name: 'Backup 1',
                description: 'Description Backup 1',
            )
            ->onDatabase('backup')
            ->execute();

        query(Publisher::class)
            ->insert(
                id: new Id(2),
                name: 'Backup 2',
                description: 'Description Backup 2',
            )
            ->onDatabase('backup')
            ->execute();

        $publishersMain = query(Publisher::class)->select()->onDatabase('main')->all();
        $publishersBackup = query(Publisher::class)->select()->onDatabase('backup')->all();

        $this->assertCount(2, $publishersMain);
        $this->assertSame('Main 1', $publishersMain[0]->name);
        $this->assertSame('Main 2', $publishersMain[1]->name);

        $this->assertCount(2, $publishersBackup);
        $this->assertSame('Backup 1', $publishersBackup[0]->name);
        $this->assertSame('Backup 2', $publishersBackup[1]->name);

        query(Publisher::class)->update(name: 'Updated Main 1')->where('id = ?', 1)->onDatabase('main')->execute();
        query(Publisher::class)->update(name: 'Updated Backup 1')->where('id = ?', 1)->onDatabase('backup')->execute();

        $this->assertSame('Updated Main 1', query(Publisher::class)->select()->where('id = ?', 1)->onDatabase('main')->first()->name);
        $this->assertSame('Updated Backup 1', query(Publisher::class)->select()->where('id = ?', 1)->onDatabase('backup')->first()->name);

        query(Publisher::class)->delete()->where('id = ?', 1)->onDatabase('main')->execute();

        $this->assertSame(1, query(Publisher::class)->count()->onDatabase('main')->execute());
        $this->assertSame(2, query(Publisher::class)->count()->onDatabase('backup')->execute());
    }

    public function test_with_different_dialects(): void
    {
        if ($this->container->get(Database::class)->dialect !== DatabaseDialect::MYSQL) {
            $this->markTestSkipped('We only test this in the MySQL test action');
        }

        $this->container->config(new SQLiteConfig(
            path: __DIR__ . '/Fixtures/main.sqlite',
            tag: 'sqlite-main',
        ));

        $this->container->config(new MysqlConfig(
            tag: 'mysql-main',
        ));

        $migrationManager = $this->container->get(MigrationManager::class);

        $migrationManager->onDatabase('sqlite-main')->executeUp(new CreateMigrationsTable());
        $migrationManager->onDatabase('mysql-main')->executeUp(new CreateMigrationsTable());

        $this->expectNotToPerformAssertions();
    }

    public function test_fails_with_unknown_connection(): void
    {
        $migrationManager = $this->container->get(MigrationManager::class);

        try {
            $migrationManager->onDatabase('unknown')->executeUp(new CreateMigrationsTable());
        } catch (TaggedDependencyCouldNotBeResolved $taggedDependencyCouldNotBeResolved) {
            $this->assertStringContainsString(
                'Could not resolve tagged dependency Tempest\Database\Config\DatabaseConfig#unknown',
                $taggedDependencyCouldNotBeResolved->getMessage(),
            );
        }
    }

    public function test_migrate_up_command(): void
    {
        $this->console
            ->call('migrate:up --database=main')
            ->assertSuccess();

        $this->assertTableExists(Migration::class, 'main');
        $this->assertTableDoesNotExist(Migration::class, 'backup');

        $this->console
            ->call('migrate:up --database=backup')
            ->assertSuccess();

        $this->assertTableExists(Migration::class, 'backup');
    }

    public function test_migrate_fresh_command(): void
    {
        $this->console
            ->call('migrate:fresh --database=main --all')
            ->assertSuccess();

        $this->assertTableExists(Migration::class, 'main');
        $this->assertTableDoesNotExist(Migration::class, 'backup');

        $this->console
            ->call('migrate:fresh --database=backup --all')
            ->assertSuccess();

        $this->assertTableExists(Migration::class, 'backup');
    }

    public function test_migrate_up_fresh_command(): void
    {
        $this->console
            ->call('migrate:up --fresh --database=main')
            ->assertSuccess();

        $this->assertTableExists(Migration::class, 'main');
        $this->assertTableDoesNotExist(Migration::class, 'backup');

        $this->console
            ->call('migrate:up --fresh --database=backup')
            ->assertSuccess();

        $this->assertTableExists(Migration::class, 'backup');
    }

    public function test_migrate_down_command(): void
    {
        $this->console
            ->call('migrate:up --database=main')
            ->assertSuccess();

        $this->console
            ->call('migrate:up --database=backup')
            ->assertSuccess();

        $this->console
            ->call('migrate:down --database=backup')
            ->assertSuccess();

        $this->assertTableExists(Migration::class, 'main');
        $this->assertTableDoesNotExist(Migration::class, 'backup');
    }

    public function test_migrate_validate_command(): void
    {
        $this->console
            ->call('migrate:validate --database=main')
            ->assertSuccess();
    }

    public function test_should_migrate(): void
    {
        /** @var MigrationManager $migrationManager */
        $migrationManager = $this->container->get(MigrationManager::class);

        $migrationManager->onDatabase('main')->executeUp(new CreateMigrationsTable());
        $migrationManager->onDatabase('backup')->executeUp(new CreateMigrationsTable());

        $migrationManager->onDatabase('main')->executeUp(new MigrationForMain());
        $migrationManager->onDatabase('main')->executeUp(new MigrationForBackup());

        $this->assertTableExists('main_table', 'main');
        $this->assertTableDoesNotExist('backup_table', 'main');

        $migrationManager->onDatabase('backup')->executeUp(new MigrationForMain());
        $migrationManager->onDatabase('backup')->executeUp(new MigrationForBackup());

        $this->assertTableExists('backup_table', 'backup');
        $this->assertTableDoesNotExist('main_table', 'backup');

        $migrationManager->onDatabase('main')->executeDown(new MigrationForMain());
        $migrationManager->onDatabase('main')->executeDown(new MigrationForBackup());
        $this->assertTableDoesNotExist('main_table', 'main');
        $this->assertTableDoesNotExist('backup_table', 'main');

        $migrationManager->onDatabase('backup')->executeDown(new MigrationForBackup());
        $migrationManager->onDatabase('backup')->executeDown(new MigrationForMain());
        $this->assertTableDoesNotExist('backup_table', 'backup');
        $this->assertTableDoesNotExist('main_table', 'backup');
    }

    public function test_database_seed_on_selected_database(): void
    {
        /** @var MigrationManager $migrationManager */
        $migrationManager = $this->container->get(MigrationManager::class);

        $migrationManager->onDatabase('main')->executeUp(new CreateMigrationsTable());
        $migrationManager->onDatabase('main')->executeUp(new CreateBookTable());
        $migrationManager->onDatabase('backup')->executeUp(new CreateMigrationsTable());
        $migrationManager->onDatabase('backup')->executeUp(new CreateBookTable());

        $this->console
            ->call('db:seed --database=main --all')
            ->assertSuccess();

        $this->assertSame(
            'Timeline Taxi',
            query(Book::class)->select()->onDatabase('main')->first()->title,
        );

        $this->assertNull(
            query(Book::class)->select()->onDatabase('backup')->first(),
        );

        $this->console
            ->call('db:seed --database=backup --all')
            ->assertSuccess();

        /** @var Book $book */
        /** @phpstan-ignore-next-line */
        $book = query(Book::class)->select()->onDatabase('backup')->first();

        $this->assertSame(
            'Timeline Taxi',
            $book->title,
        );
    }

    public function test_migrate_fresh_seed_on_selected_database(): void
    {
        $this->console
            ->call('migrate:fresh --seed --database=main --all')
            ->assertSuccess();

        $this->assertSame(
            'Timeline Taxi',
            query(Book::class)->select()->onDatabase('main')->first()->title,
        );

        $this->assertException(PDOException::class, function (): void {
            query(Book::class)->select()->onDatabase('backup')->first();
        });

        $this->console
            ->call('migrate:fresh --seed --database=backup --all')
            ->assertSuccess();

        $this->assertSame(
            'Timeline Taxi',
            query(Book::class)->select()->onDatabase('backup')->first()->title,
        );
    }

    private function assertTableExists(string $tableName, string $onDatabase): void
    {
        $this->assertTrue(query($tableName)->count()->onDatabase($onDatabase)->execute() >= 0);
    }

    private function assertTableDoesNotExist(string $tableName, string $onDatabase): void
    {
        $this->assertException(
            PDOException::class,
            fn () => query($tableName)->count()->onDatabase($onDatabase)->execute(),
        );
    }
}
