<?php

namespace Tempest\Database\Tests\Compilers\Dialects\Postgres;

use PHPUnit\Framework\TestCase;
use Tempest\Database\Columns\CharColumn;
use Tempest\Database\Columns\DateColumn;
use Tempest\Database\Columns\DateTimeColumn;
use Tempest\Database\Columns\FloatColumn;
use Tempest\Database\Columns\IntegerColumn;
use Tempest\Database\Columns\TextColumn;
use Tempest\Database\Columns\UuidColumn;
use Tempest\Database\Columns\VarcharColumn;
use Tempest\Database\Compilers\Dialects\Postgres\PostgresColumnCompiler;

final class PostgresColumnCompilerTest extends TestCase
{
    public function test_char_compilation()
    {
        $column = new CharColumn('test');

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"test" CHAR(1) NOT NULL', $compiler->compile($column));
    }

    public function test_char_compilation_with_length()
    {
        $column = new CharColumn('test')->length(10);

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"test" CHAR(10) NOT NULL', $compiler->compile($column));
    }

    public function test_date_compilation()
    {
        $column = new DateColumn('birthday');

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"birthday" DATE NOT NULL', $compiler->compile($column));
    }

    public function test_datetime_compilation()
    {
        $column = new DateTimeColumn('created_at');

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"created_at" TIMESTAMP(0) NOT NULL', $compiler->compile($column));
    }

    public function test_datetime_compilation_with_precision()
    {
        $column = new DateTimeColumn('created_at')->precision(6);

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"created_at" TIMESTAMP(6) NOT NULL', $compiler->compile($column));
    }

    public function test_datetime_compilation_with_current()
    {
        $column = new DateTimeColumn('created_at')->current();

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"created_at" TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0)', $compiler->compile($column));
    }

    public function test_datetime_compilation_with_current_on_update()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The "currentOnUpdate" method is not supported by the Postgres dialect.');

        $column = new DateTimeColumn('created_at')->currentOnUpdate();

        new PostgresColumnCompiler()->compile($column);
    }

    public function test_float_compilation()
    {
        $column = new FloatColumn('temperature');

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"temperature" REAL NOT NULL', $compiler->compile($column));
    }

    public function test_integer_compilation()
    {
        $column = new IntegerColumn('age');

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"age" INT NOT NULL', $compiler->compile($column));
    }

    public function test_unsigned_integer_compilation()
    {
        $column = new IntegerColumn('age')->unsigned();

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals(
            '"age" INT NOT NULL CHECK ("age" >= 0)',
            $compiler->compile($column),
        );
    }

    public function test_tiny_text_compilation()
    {
        $column = new TextColumn('description')->length(255);

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"description" TEXT NOT NULL', $compiler->compile($column));
    }

    public function test_text_compilation()
    {
        $column = new TextColumn('description')->length(65_535);

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"description" TEXT NOT NULL', $compiler->compile($column));
    }

    public function test_medium_text_compilation()
    {
        $column = new TextColumn('description')->length(16_777_215);

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"description" TEXT NOT NULL', $compiler->compile($column));
    }

    public function test_long_text_compilation()
    {
        $column = new TextColumn('description')->length(4_294_967_295);

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"description" TEXT NOT NULL', $compiler->compile($column));
    }

    public function test_uuid_compilation()
    {
        $column = new UuidColumn('uuid');

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"uuid" UUID NOT NULL', $compiler->compile($column));
    }

    public function test_uuid_compilation_with_generated_uuid()
    {
        $column = new UuidColumn('uuid')->generated();

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"uuid" UUID NOT NULL DEFAULT gen_random_uuid()', $compiler->compile($column));
    }

    public function test_varchar_compilation()
    {
        $column = new VarcharColumn('note');

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"note" VARCHAR(255) NOT NULL', $compiler->compile($column));
    }

    public function test_varchar_compilation_with_length()
    {
        $column = new VarcharColumn('note')->length(100);

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"note" VARCHAR(100) NOT NULL', $compiler->compile($column));
    }

    public function test_primary_key_constraint_compilation()
    {
        $column = new IntegerColumn('id')->primaryKey();

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals(
            '"id" INT NOT NULL PRIMARY KEY',
            $compiler->compile($column)
        );
    }

    public function test_unique_constraint_compilation()
    {
        $column = new VarcharColumn('name')->unique();

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"name" VARCHAR(255) NOT NULL UNIQUE', $compiler->compile($column));
    }

    public function test_nullability_constraint_compilation()
    {
        $default = new VarcharColumn('name');
        $column1 = new VarcharColumn('name')->nullable();
        $column2 = new VarcharColumn('name')->nullable(false);

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"name" VARCHAR(255) NOT NULL', $compiler->compile($default));
        $this->assertEquals('"name" VARCHAR(255) NULL', $compiler->compile($column1));
        $this->assertEquals('"name" VARCHAR(255) NOT NULL', $compiler->compile($column2));
    }

    public function test_auto_increment_attribute_compilation()
    {
        $column = new IntegerColumn('id')->autoIncrement();

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"id" INT NOT NULL GENERATED BY DEFAULT AS IDENTITY', $compiler->compile($column));
    }

    public function test_auto_increment_primary_key_compilation()
    {
        $column = new IntegerColumn('id')->autoIncrement()->primaryKey();

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals(
            '"id" INT NOT NULL GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY',
            $compiler->compile($column),
        );
    }

    public function test_default_value_compilation()
    {
        $column = new VarcharColumn('name')->default('Jim Halpert');

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals('"name" VARCHAR(255) NOT NULL DEFAULT \'Jim Halpert\'', $compiler->compile($column));
    }

    public function test_default_value_escaping()
    {
        $column = new VarcharColumn('name')->default("O'Reilly");

        $compiler = new PostgresColumnCompiler();

        $this->assertEquals(
            "\"name\" VARCHAR(255) NOT NULL DEFAULT 'O''Reilly'",
            $compiler->compile($column),
        );
    }
}