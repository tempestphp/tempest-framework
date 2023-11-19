<?php

declare(strict_types=1);

namespace App\Modules\Books\Models;

use Tempest\Interface\Model;
use Tempest\ORM\BaseModel;

class Chapter implements Model
{
    use BaseModel;

    public string $title;

    public string $contents;

    public Book $book;
}
