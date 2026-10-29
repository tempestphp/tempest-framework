<?php

namespace Tempest\Database\Tests\Compilers\Dialects\MySQL;

use PHPUnit\Framework\TestCase;
use Tempest\Database\Columns\IntegerColumn;
use Tempest\Database\Columns\TextColumn;
use Tempest\Database\Compilers\Dialects\MySQL\MySQLColumnCompiler;
use Tempest\Database\Compilers\Dialects\MySQL\MySQLTableCompiler;
use Tempest\Database\TableDefinitions\CreateTable;

final class MySQLTableCompilerTestCase extends TestCase
{
    public function test_compile_create_table()
    {
        $compiler = new MySQLTableCompiler(new MySQLColumnCompiler());
        $createTable = new CreateTable('users')->addColumns(
            new IntegerColumn('id')
                ->unsigned()
                ->autoIncrement()
                ->primaryKey(),
            new TextColumn('first_name'),
            new TextColumn('middle_name')->nullable(),
            new TextColumn('last_name'),
        );

        $this->assertEquals(
            <<<SQL
            CREATE TABLE `users` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `first_name` TEXT NOT NULL,
                `middle_name` TEXT NULL,
                `last_name` TEXT NOT NULL
            );
            SQL,
            $compiler->compile($createTable)
        );
    }
}