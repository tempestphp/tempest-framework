<?php

namespace App\Migrations;

use App\Modules\Books\Author;
use App\Modules\Books\Book;
use Tempest\Database\Builder\IdRow;
use Tempest\Database\Builder\IntRow;
use Tempest\Database\Builder\TableBuilder;
use Tempest\Database\Builder\TextRow;
use Tempest\Interfaces\Migration;

final readonly class CreateAuthorTable implements Migration
{
    public function getName(): string
    {
        return '0000-00-00_create_author_table';
    }

    public function up(TableBuilder $builder): TableBuilder
    {
        return $builder
            ->name(Author::table())
            ->add(new IdRow())
            ->add(new TextRow('name'))
            ->create();
    }

    public function down(TableBuilder $builder): TableBuilder
    {
        return $builder->name(Book::table())->drop();
    }
}
