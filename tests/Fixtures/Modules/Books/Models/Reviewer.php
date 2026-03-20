<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\IsDatabaseModel;

final class Reviewer
{
    use IsDatabaseModel;

    public string $name;

    public ?BookReview $bookReview = null;
}
