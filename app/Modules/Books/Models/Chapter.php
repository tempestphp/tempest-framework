<?php

declare(strict_types=1);

namespace App\Modules\Books\Models;

use Tempest\ORM\IsModel;
use Tempest\ORM\Model;

class Chapter implements Model
{
    use IsModel;

    public string $title;

    public string $contents;

    public Book $book;
}
