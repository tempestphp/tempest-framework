<?php

declare(strict_types=1);

namespace App\Modules\Books\Models;

use Tempest\Database\IsModel;
use Tempest\Database\Model;

class Chapter implements Model
{
    use IsModel;

    public string $title;

    public string $contents;

    public Book $book;
}
