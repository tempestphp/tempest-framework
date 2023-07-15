<?php

declare(strict_types=1);

namespace App\Modules\Books;

use Tempest\Interfaces\Model;
use Tempest\ORM\Attributes\Lazy;
use Tempest\ORM\BaseModel;

class Chapter implements Model
{
    use BaseModel;

    #[Lazy] public Book $book;

    public string $title;

    public string $contents;
}
