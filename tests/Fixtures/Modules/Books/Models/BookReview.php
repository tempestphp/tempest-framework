<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\IsDatabaseModel;

final class BookReview
{
    use IsDatabaseModel;

    public string $content;

    public ?Tag $tag = null;
}
