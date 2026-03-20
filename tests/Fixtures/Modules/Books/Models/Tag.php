<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\BelongsToMany;
use Tempest\Database\HasManyThrough;
use Tempest\Database\HasOneThrough;
use Tempest\Database\IsDatabaseModel;

final class Tag
{
    use IsDatabaseModel;

    public string $label;

    /** @var \Tests\Tempest\Fixtures\Modules\Books\Models\Book[] */
    #[BelongsToMany]
    public array $books = [];

    /** @var \Tests\Tempest\Fixtures\Modules\Books\Models\Reviewer[] */
    #[HasManyThrough(through: BookReview::class)]
    public array $reviewers = [];

    #[HasOneThrough(through: BookReview::class)]
    public ?Reviewer $topReviewer = null;
}
