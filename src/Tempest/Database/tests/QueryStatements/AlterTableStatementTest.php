<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\BelongsToStatement;
use Tempest\Database\QueryStatements\VarcharStatement;
use Tempest\Database\UnsupportedDialect;

/**
 * @internal
 */
#[CoversClass(AlterTableStatement::class)]
final class AlterTableStatementTest extends TestCase
{
    #[TestWith([DatabaseDialect::MYSQL])]
    #[TestWith([DatabaseDialect::POSTGRESQL])]
    #[TestWith([DatabaseDialect::SQLITE])]
    public function test_alter_for_only_indexes(DatabaseDialect $dialect): void
    {
        $expected = 'CREATE INDEX `table_foo` ON `table` (`foo`); CREATE UNIQUE INDEX `table_bar` ON `table` (`bar`);';
        $statement = new AlterTableStatement('table')
            ->index('foo')
            ->unique('bar')
            ->compile($dialect);

        $normalized = self::removeDuplicateWhitespace($statement);

        $this->assertEqualsIgnoringCase($expected, $normalized);
    }

    #[TestWith([DatabaseDialect::MYSQL])]
    #[TestWith([DatabaseDialect::POSTGRESQL])]
    #[TestWith([DatabaseDialect::SQLITE])]
    public function test_alter_add_column(DatabaseDialect $dialect): void
    {
        $expected = 'ALTER TABLE `table` ADD `bar` VARCHAR(42) DEFAULT "xx" ;';
        $statement = new AlterTableStatement('table')
            ->add(new VarcharStatement('bar', 42, true, 'xx'))
            ->compile($dialect);

        $normalized = self::removeDuplicateWhitespace($statement);

        $this->assertEqualsIgnoringCase($expected, $normalized);
    }

    #[TestWith([DatabaseDialect::MYSQL])]
    #[TestWith([DatabaseDialect::POSTGRESQL])]
    public function test_alter_add_belongs_to(DatabaseDialect $dialect): void
    {
        $expected = 'ALTER TABLE `table` ADD CONSTRAINT `fk_parent_table_foo` FOREIGN KEY table(foo) REFERENCES parent(bar) ON DELETE RESTRICT ON UPDATE NO ACTION ;';
        $statement = new AlterTableStatement('table')
            ->add(new BelongsToStatement('table.foo', 'parent.bar'))
            ->compile($dialect);

        $normalized = self::removeDuplicateWhitespace($statement);

        $this->assertEqualsIgnoringCase($expected, $normalized);
    }

    #[TestWith([DatabaseDialect::SQLITE])]
    public function test_alter_add_belongs_to_unsupported(DatabaseDialect $dialect): void
    {
        $this->expectException(UnsupportedDialect::class);

        new AlterTableStatement('table')
            ->add(new BelongsToStatement('table.foo', 'parent.bar'))
            ->compile($dialect);
    }

    #[TestWith([DatabaseDialect::MYSQL])]
    #[TestWith([DatabaseDialect::POSTGRESQL])]
    #[TestWith([DatabaseDialect::SQLITE])]
    public function test_alter_table_drop_column(DatabaseDialect $dialect): void
    {
        $expected = 'ALTER TABLE `table` DROP COLUMN `foo` ;';
        $statement = new AlterTableStatement('table')
            ->dropColumn('foo')
            ->compile($dialect);

        $normalized = self::removeDuplicateWhitespace($statement);

        $this->assertEqualsIgnoringCase($expected, $normalized);
    }

    #[TestWith([DatabaseDialect::MYSQL, 'ALTER TABLE `table` DROP CONSTRAINT `foo` ;'])]
    #[TestWith([DatabaseDialect::POSTGRESQL, 'ALTER TABLE `table` DROP CONSTRAINT `foo` ;'])]
    public function test_alter_table_drop_constraint(DatabaseDialect $dialect, string $expected): void
    {
        $statement = new AlterTableStatement('table')
            ->dropConstraint('foo')
            ->compile($dialect);

        $normalized = self::removeDuplicateWhitespace($statement);

        $this->assertEqualsIgnoringCase($expected, $normalized);
    }

    #[TestWith([DatabaseDialect::SQLITE])]
    public function test_alter_table_drop_constraint_unsupported_dialects(DatabaseDialect $dialect): void
    {
        $this->expectException(UnsupportedDialect::class);
        new AlterTableStatement('table')
            ->dropConstraint('foo')
            ->compile($dialect);
    }

    #[TestWith([DatabaseDialect::MYSQL, 'ALTER TABLE `table` ADD `foo` VARCHAR(42) DEFAULT "bar" NOT NULL ;'])]
    #[TestWith([
        DatabaseDialect::POSTGRESQL,
        'ALTER TABLE `table` ADD `foo` VARCHAR(42) DEFAULT "bar" NOT NULL ;',
    ])]
    #[TestWith([DatabaseDialect::SQLITE, 'ALTER TABLE `table` ADD `foo` VARCHAR(42) DEFAULT "bar" NOT NULL ;'])]
    public function test_alter_table_add_column(DatabaseDialect $dialect, string $expected): void
    {
        $statement = new AlterTableStatement('table')
            ->add(new VarcharStatement('foo', 42, false, 'bar'))
            ->compile($dialect);

        $normalized = self::removeDuplicateWhitespace($statement);

        $this->assertEqualsIgnoringCase($expected, $normalized);
    }

    #[TestWith([DatabaseDialect::MYSQL])]
    #[TestWith([DatabaseDialect::POSTGRESQL])]
    #[TestWith([DatabaseDialect::SQLITE])]
    public function test_alter_table_rename_column(DatabaseDialect $dialect): void
    {
        $expected = 'ALTER TABLE `table` RENAME COLUMN `foo` TO `bar` ;';
        $statement = new AlterTableStatement('table')
            ->rename('foo', 'bar')
            ->compile($dialect);

        $normalized = self::removeDuplicateWhitespace($statement);

        $this->assertEqualsIgnoringCase($expected, $normalized);
    }

    #[TestWith([DatabaseDialect::MYSQL, 'ALTER TABLE `table` MODIFY COLUMN `foo` VARCHAR(42) DEFAULT "bar" NOT NULL ;'])]
    #[TestWith([DatabaseDialect::POSTGRESQL, 'ALTER TABLE `table` ALTER COLUMN `foo` VARCHAR(42) DEFAULT "bar" NOT NULL ;'])]
    public function test_alter_table_modify_column(DatabaseDialect $dialect, string $expected): void
    {
        $statement = new AlterTableStatement('table')
            ->modify(new VarcharStatement('foo', 42, false, 'bar'))
            ->compile($dialect);

        $normalized = self::removeDuplicateWhitespace($statement);

        $this->assertEqualsIgnoringCase($expected, $normalized);
    }

    #[TestWith([DatabaseDialect::SQLITE])]
    public function test_alter_table_modify_column_unsupported(DatabaseDialect $dialect): void
    {
        $this->expectException(UnsupportedDialect::class);

        new AlterTableStatement('table')
            ->modify(new VarcharStatement('foo', 42, false, 'bar'))
            ->compile($dialect);
    }

    private static function removeDuplicateWhitespace(string $string): string
    {
        return trim(preg_replace('/(\n|\s)+/m', ' ', $string));
    }
}
