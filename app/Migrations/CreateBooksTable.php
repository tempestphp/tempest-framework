<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Modules\Books\Book;
use Tempest\Database\TableBuilder\IdRow;
use Tempest\Database\TableBuilder\IntRow;
use Tempest\Database\TableBuilder\TableBuilder;
use Tempest\Database\TableBuilder\TextRow;
use Tempest\Interfaces\DatabaseMigration;

final readonly class CreateBooksTable implements DatabaseMigration
{
    public function getName(): string
    {
        return '0000-00-00_create_books_table';
    }

    public function up(TableBuilder $builder): TableBuilder
    {
        return $builder
            ->name(Book::table())
            ->add(new IdRow())
            ->add(new TextRow('title'))
            ->add(new IntRow('author_id'))
            ->create();
    }

    public function down(TableBuilder $builder): TableBuilder
    {
        return $builder->name(Book::table())->drop();
    }
}
