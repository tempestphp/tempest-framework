<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\IsModel;
use Tempest\Database\Model;

class Chapter implements Model
{
    use IsModel;

    public string $title;

    public string $contents;

    public Book $book;
}
