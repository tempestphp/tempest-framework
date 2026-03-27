<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Modules\Books\Models\BookReview;

final class CreateBookReviewTable implements MigratesUp, MigratesDown
{
    private(set) string $name = '0000-00-12_create_book_reviews_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: BookReview::class)
            ->primary()
            ->text(name: 'content')
            ->belongsTo(local: 'book_reviews.tag_id', foreign: 'tags.id');
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(modelClass: BookReview::class);
    }
}
