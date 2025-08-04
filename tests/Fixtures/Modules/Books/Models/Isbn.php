<?php

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;

final class Isbn
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public string $value;

    public Book $book;
}
