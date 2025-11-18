<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Commands;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class MakeMigrationCommandTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->installer->configure(
            $this->internalStorage . '/install',
            new Psr4Namespace('App\\', $this->internalStorage . '/install/App'),
        );
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        $this->installer->clean();
    }

    #[Test]
    #[DataProvider('command_input_provider')]
    public function make_command(string $commandArgs, string $expectedPath, string $expectedNamespace, string $expectContains): void
    {
        $this->console
            ->call("make:migration {$commandArgs}")
            ->submit();

        $this->installer
            ->assertFileExists($expectedPath)
            ->assertFileNotContains($expectedPath, 'SkipDiscovery')
            ->assertFileContains($expectedPath, 'namespace ' . $expectedNamespace . ';')
            ->assertFileContains($expectedPath, $expectContains);
    }

    public static function command_input_provider(): array
    {
        return [
            'make_with_defaults' => [
                'commandArgs' => 'Books',
                'expectedPath' => 'App/CreateBooksTable.php',
                'expectedNamespace' => 'App',
                'expectContains' => 'MigratesUp, MigratesDown',
            ],
            'make_up' => [
                'commandArgs' => 'CreateBooksTable up',
                'expectedPath' => 'App/CreateBooksTable.php',
                'expectedNamespace' => 'App',
                'expectContains' => 'MigratesUp',
            ],
            'make_with_other_namespace' => [
                'commandArgs' => 'Books\\CreateBooksTable',
                'expectedPath' => 'App/Books/CreateBooksTable.php',
                'expectedNamespace' => 'App\\Books',
                'expectContains' => 'MigratesUp, MigratesDown',
            ],
            'make_with_input_path' => [
                'commandArgs' => 'Books/CreateBooksTable',
                'expectedPath' => 'App/Books/CreateBooksTable.php',
                'expectedNamespace' => 'App\\Books',
                'expectContains' => 'MigratesUp, MigratesDown',
            ],
        ];
    }

    #[Test]
    #[TestWith(['create_books_table', 'create_books_table'])]
    #[TestWith(['books', 'create_books_table'])]
    public function raw_migration(string $filename, string $expectedFilename): void
    {
        $this->console
            ->call("make:migration {$filename} raw")
            ->submit();

        $filePath = sprintf('App/%s_%s.sql', date('Y-m-d'), $expectedFilename);

        $this->installer
            ->assertFileExists($filePath)
            ->assertFileContains($filePath, 'CREATE TABLE books');
    }
}
