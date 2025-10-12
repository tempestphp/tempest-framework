<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\IsDatabaseModel;

final class Chapter
{
    use IsDatabaseModel;

    public string $title;

    public ?string $contents;

    public Book $book;
}
