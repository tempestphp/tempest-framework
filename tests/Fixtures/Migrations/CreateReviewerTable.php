<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Modules\Books\Models\Reviewer;

final class CreateReviewerTable implements MigratesUp, MigratesDown
{
    private(set) string $name = '0000-00-13_create_reviewers_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(modelClass: Reviewer::class)
            ->primary()
            ->text(name: 'name')
            ->belongsTo(local: 'reviewers.book_review_id', foreign: 'book_reviews.id');
    }

    public function down(): QueryStatement
    {
        return DropTableStatement::forModel(modelClass: Reviewer::class);
    }
}
