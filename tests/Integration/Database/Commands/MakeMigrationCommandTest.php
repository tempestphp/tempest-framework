<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Commands;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Tables\PascalCaseStrategy;
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
    public function object_create_migration_implements_both_interfaces(): void
    {
        $this->console
            ->call('make:migration Books class --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php')
            ->assertFileContains('App/CreateBooksTable.php', 'MigratesUp, MigratesDown')
            ->assertFileNotContains('App/CreateBooksTable.php', 'SkipDiscovery');
    }

    #[Test]
    public function object_create_migration_has_create_and_drop_statements(): void
    {
        $this->console
            ->call('make:migration Books class --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileContains('App/CreateBooksTable.php', 'CreateTableStatement')
            ->assertFileContains('App/CreateBooksTable.php', 'DropTableStatement')
            ->assertFileContains('App/CreateBooksTable.php', "'books'");
    }

    #[Test]
    public function object_create_migration_includes_timestamps(): void
    {
        $this->console
            ->call('make:migration Books class --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileContains('App/CreateBooksTable.php', 'created_at')
            ->assertFileContains('App/CreateBooksTable.php', 'updated_at');
    }

    #[Test]
    public function object_create_migration_has_date_prefixed_name_property(): void
    {
        $this->console
            ->call('make:migration Books class --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileContains('App/CreateBooksTable.php', sprintf("'%s_create_books_table'", date('Y-m-d')));
    }

    #[Test]
    public function object_create_migration_sets_correct_namespace(): void
    {
        $this->console
            ->call('make:migration Books class --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileContains('App/CreateBooksTable.php', 'namespace App;');
    }

    #[Test]
    public function object_alter_migration_implements_both_interfaces(): void
    {
        $this->console
            ->call('make:migration AddShortSummaryToBooks class --table=books --alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/AddShortSummaryToBooks.php')
            ->assertFileContains('App/AddShortSummaryToBooks.php', 'MigratesUp, MigratesDown');
    }

    #[Test]
    public function object_alter_migration_uses_alter_table_statement(): void
    {
        $this->console
            ->call('make:migration AddShortSummaryToBooks class --table=books --alter')
            ->submit();

        $this->installer
            ->assertFileContains('App/AddShortSummaryToBooks.php', 'AlterTableStatement')
            ->assertFileContains('App/AddShortSummaryToBooks.php', "'books'")
            ->assertFileNotContains('App/AddShortSummaryToBooks.php', 'CreateTableStatement')
            ->assertFileNotContains('App/AddShortSummaryToBooks.php', 'DropTableStatement');
    }

    #[Test]
    public function up_create_migration_only_implements_migrates_up(): void
    {
        $this->console
            ->call('make:migration CreateBooksTable up --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php')
            ->assertFileContains('App/CreateBooksTable.php', 'MigratesUp')
            ->assertFileNotContains('App/CreateBooksTable.php', 'MigratesDown');
    }

    #[Test]
    public function up_create_migration_has_create_table_statement(): void
    {
        $this->console
            ->call('make:migration CreateBooksTable up --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileContains('App/CreateBooksTable.php', 'CreateTableStatement')
            ->assertFileContains('App/CreateBooksTable.php', "'books'");
    }

    #[Test]
    public function up_alter_migration_has_alter_statement_without_down(): void
    {
        $this->console
            ->call('make:migration SomeMigration up --table=books --alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/SomeMigration.php')
            ->assertFileContains('App/SomeMigration.php', 'MigratesUp')
            ->assertFileContains('App/SomeMigration.php', 'AlterTableStatement')
            ->assertFileContains('App/SomeMigration.php', "'books'")
            ->assertFileNotContains('App/SomeMigration.php', 'MigratesDown');
    }

    #[Test]
    #[TestWith(['create_books_table', 'create_books_table'])]
    #[TestWith(['books', 'create_books_table'])]
    public function raw_create_migration(string $filename, string $expectedFilename): void
    {
        $this->console
            ->call("make:migration {$filename} raw --table=books --no-alter")
            ->submit();

        $filePath = sprintf('App/%s_%s.sql', date('Y-m-d'), $expectedFilename);

        $this->installer
            ->assertFileExists($filePath)
            ->assertFileContains($filePath, 'CREATE TABLE books');
    }

    #[Test]
    public function raw_alter_migration_produces_alter_sql(): void
    {
        $this->console
            ->call('make:migration add_short_summary_to_books raw --table=books --alter')
            ->submit();

        $filePath = sprintf('App/%s_add_short_summary_to_books.sql', date('Y-m-d'));

        $this->installer
            ->assertFileExists($filePath)
            ->assertFileContains($filePath, 'ALTER TABLE books')
            ->assertFileNotContains($filePath, 'CREATE TABLE');
    }

    #[Test]
    public function simple_name_gets_create_table_wrapping(): void
    {
        $this->console
            ->call('make:migration Book class --table=book --no-alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php')
            ->assertFileContains('App/CreateBooksTable.php', 'CreateTableStatement')
            ->assertFileContains('App/CreateBooksTable.php', "'books'");
    }

    #[Test]
    public function plural_name_gets_create_table_wrapping(): void
    {
        $this->console
            ->call('make:migration Books class --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php');
    }

    #[Test]
    public function table_name_is_pluralized_from_singular_input(): void
    {
        $this->console
            ->call('make:migration CreateBookTable up --table=book --no-alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php')
            ->assertFileContains('App/CreateBooksTable.php', "'books'");
    }

    #[Test]
    public function alter_migration_name_is_not_wrapped(): void
    {
        $this->console
            ->call('make:migration SomeMigration up --table=books --alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/SomeMigration.php');
    }

    #[Test]
    public function already_complete_name_is_not_double_wrapped(): void
    {
        $this->console
            ->call('make:migration CreateBooksTable class --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php')
            ->assertFileNotContains('App/CreateBooksTable.php', 'CreateCreateBooksTable');
    }

    #[Test]
    public function backslash_namespace_creates_subdirectory(): void
    {
        $this->console
            ->call('make:migration Books\\CreateBooksTable class --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/Books/CreateBooksTable.php')
            ->assertFileContains('App/Books/CreateBooksTable.php', 'namespace App\\Books;');
    }

    #[Test]
    public function forward_slash_path_creates_subdirectory(): void
    {
        $this->console
            ->call('make:migration Books/CreateBooksTable class --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/Books/CreateBooksTable.php')
            ->assertFileContains('App/Books/CreateBooksTable.php', 'namespace App\\Books;');
    }

    #[Test]
    public function deeply_nested_namespace(): void
    {
        $this->console
            ->call('make:migration Database/Migrations/Books/CreateBooksTable class --table=books --no-alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/Database/Migrations/Books/CreateBooksTable.php')
            ->assertFileContains('App/Database/Migrations/Books/CreateBooksTable.php', 'namespace App\\Database\\Migrations\\Books;');
    }

    #[Test]
    public function yes_flag_defaults_to_object_type(): void
    {
        $this->console
            ->call('make:migration Books -y')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php')
            ->assertFileContains('App/CreateBooksTable.php', 'MigratesUp, MigratesDown')
            ->assertFileContains('App/CreateBooksTable.php', "'books'");
    }

    #[Test]
    public function yes_flag_with_explicit_table(): void
    {
        $this->console
            ->call('make:migration Books -y --table=novels')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php')
            ->assertFileContains('App/CreateBooksTable.php', "'novels'");
    }

    #[Test]
    public function yes_flag_with_explicit_up_type(): void
    {
        $this->console
            ->call('make:migration CreateBooksTable up -y')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php')
            ->assertFileContains('App/CreateBooksTable.php', 'MigratesUp')
            ->assertFileNotContains('App/CreateBooksTable.php', 'MigratesDown');
    }

    #[Test]
    public function yes_flag_guesses_create_from_name(): void
    {
        $this->console
            ->call('make:migration CreateUsersTable -y')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/CreateUsersTable.php')
            ->assertFileContains('App/CreateUsersTable.php', 'CreateTableStatement')
            ->assertFileContains('App/CreateUsersTable.php', "'users'");
    }

    #[Test]
    public function yes_flag_guesses_alter_from_to_preposition(): void
    {
        $this->console
            ->call('make:migration AddSummaryToBooks -y')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/AddSummaryToBooks.php')
            ->assertFileContains('App/AddSummaryToBooks.php', 'AlterTableStatement')
            ->assertFileContains('App/AddSummaryToBooks.php', "'books'");
    }

    #[Test]
    public function yes_flag_guesses_alter_from_from_preposition(): void
    {
        $this->console
            ->call('make:migration RemoveColumnFromBooks -y')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/RemoveColumnFromBooks.php')
            ->assertFileContains('App/RemoveColumnFromBooks.php', 'AlterTableStatement')
            ->assertFileContains('App/RemoveColumnFromBooks.php', "'books'");
    }

    #[Test]
    public function yes_flag_guesses_alter_from_in_preposition(): void
    {
        $this->console
            ->call('make:migration UpdateStatusInOrders -y')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/UpdateStatusInOrders.php')
            ->assertFileContains('App/UpdateStatusInOrders.php', 'AlterTableStatement')
            ->assertFileContains('App/UpdateStatusInOrders.php', "'orders'");
    }

    #[Test]
    public function yes_flag_without_name_shows_error(): void
    {
        $this->console
            ->call('make:migration -y')
            ->assertContains('required');
    }

    #[Test]
    public function yes_flag_with_raw_type(): void
    {
        $this->console
            ->call('make:migration create_books raw -y')
            ->assertSuccess();

        $filePath = sprintf('App/%s_create_books_table.sql', date('Y-m-d'));

        $this->installer
            ->assertFileExists($filePath)
            ->assertFileContains($filePath, 'CREATE TABLE books');
    }

    #[Test]
    public function yes_flag_with_raw_alter_type(): void
    {
        $this->console
            ->call('make:migration add_column_to_books raw -y')
            ->assertSuccess();

        $filePath = sprintf('App/%s_add_column_to_books.sql', date('Y-m-d'));

        $this->installer
            ->assertFileExists($filePath)
            ->assertFileContains($filePath, 'ALTER TABLE books');
    }

    #[Test]
    public function yes_flag_with_unguessable_name_defaults_to_create(): void
    {
        $this->console
            ->call('make:migration Foo -y')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/CreateFoosTable.php')
            ->assertFileContains('App/CreateFoosTable.php', 'CreateTableStatement');
    }

    #[Test]
    public function yes_flag_explicit_alter_overrides_create_guess(): void
    {
        $this->console
            ->call('make:migration CreateBooks -y --alter')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/CreateBooks.php')
            ->assertFileContains('App/CreateBooks.php', 'AlterTableStatement')
            ->assertFileNotContains('App/CreateBooks.php', 'CreateTableStatement');
    }

    #[Test]
    public function yes_flag_explicit_no_alter_overrides_alter_guess(): void
    {
        $this->console
            ->call('make:migration AddSummaryToBooks -y --no-alter')
            ->assertSuccess();

        $this->installer
            ->assertFileContains('App/CreateAddSummaryToBooksTable.php', 'CreateTableStatement')
            ->assertFileNotContains('App/CreateAddSummaryToBooksTable.php', 'AlterTableStatement');
    }

    #[Test]
    public function create_migration_respects_pascal_case_strategy(): void
    {
        $this->container->config(new SQLiteConfig(
            namingStrategy: new PascalCaseStrategy(),
        ));

        $this->console
            ->call('make:migration CreateBookTable up --table=book --no-alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php')
            ->assertFileContains('App/CreateBooksTable.php', "'Book'");
    }

    #[Test]
    public function yes_flag_respects_pascal_case_strategy(): void
    {
        $this->container->config(new SQLiteConfig(
            namingStrategy: new PascalCaseStrategy(),
        ));

        $this->console
            ->call('make:migration Books -y')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php')
            ->assertFileContains('App/CreateBooksTable.php', "'Book'");
    }

    #[Test]
    public function alter_migration_respects_pascal_case_strategy(): void
    {
        $this->container->config(new SQLiteConfig(
            namingStrategy: new PascalCaseStrategy(),
        ));

        $this->console
            ->call('make:migration AddSummaryToBooks up --table=books --alter')
            ->submit();

        $this->installer
            ->assertFileExists('App/AddSummaryToBooks.php')
            ->assertFileContains('App/AddSummaryToBooks.php', "'Book'");
    }

    #[Test]
    public function raw_migration_respects_pascal_case_strategy(): void
    {
        $this->container->config(new SQLiteConfig(
            namingStrategy: new PascalCaseStrategy(),
        ));

        $this->console
            ->call('make:migration create_books raw --table=book --no-alter')
            ->submit();

        $filePath = sprintf('App/%s_create_books_table.sql', date('Y-m-d'));

        $this->installer
            ->assertFileExists($filePath)
            ->assertFileContains($filePath, 'CREATE TABLE Book');
    }

    #[Test]
    #[TestWith(['migration:make'])]
    #[TestWith(['migration:create'])]
    #[TestWith(['create:migration'])]
    public function command_aliases_work(string $alias): void
    {
        $this->console
            ->call("{$alias} Books -y")
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/CreateBooksTable.php');
    }

    #[Test]
    public function displays_success_message_on_creation(): void
    {
        $this->console
            ->call('make:migration Books -y')
            ->assertSuccess()
            ->assertContains('successfully created');
    }
}
