<?php

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\IsDatabaseModel;

final class Isbn
{
    use IsDatabaseModel;

    public string $value;

    public Book $book;
}
