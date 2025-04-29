<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Commands;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Support\Namespace\Psr4Namespace;

/**
 * @internal
 */
final class MakeMigrationCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer->configure(
            __DIR__ . '/install',
            new Psr4Namespace('App\\', __DIR__ . '/install/App'),
        );
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    #[Test]
    #[DataProvider('command_input_provider')]
    public function make_command(
        string $commandArgs,
        string $expectedPath,
        string $expectedNamespace,
    ): void {
        $this->console
            ->call("make:migration {$commandArgs}")
            ->submit();

        $this->installer
            ->assertFileExists($expectedPath)
            ->assertFileNotContains($expectedPath, 'SkipDiscovery')
            ->assertFileContains($expectedPath, 'namespace ' . $expectedNamespace . ';');
    }

    public static function command_input_provider(): array
    {
        return [
            'make_with_defaults' => [
                'commandArgs' => 'BookMigration',
                'expectedPath' => 'App/BookMigration.php',
                'expectedNamespace' => 'App',
            ],
            'make_with_other_namespace' => [
                'commandArgs' => 'Books\\BookMigration',
                'expectedPath' => 'App/Books/BookMigration.php',
                'expectedNamespace' => 'App\\Books',
            ],
            'make_with_input_path' => [
                'commandArgs' => 'Books/BookMigration',
                'expectedPath' => 'App/Books/BookMigration.php',
                'expectedNamespace' => 'App\\Books',
            ],
        ];
    }

    #[Test]
    public function raw_migration(): void
    {
        $this->console
            ->call('make:migration book_migration raw')
            ->submit();

        $filePath = sprintf('App/%s_book_migration.sql', date('Y-m-d'));
        $this->installer
            ->assertFileExists($filePath)
            ->assertFileContains($filePath, 'CREATE TABLE book_migration');
    }
}
