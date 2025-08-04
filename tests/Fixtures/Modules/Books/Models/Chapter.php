<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;

final class Chapter
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public string $title;

    public ?string $contents;

    public Book $book;
}
