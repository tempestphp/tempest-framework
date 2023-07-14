<?php

namespace App\Modules\Books;

use Tempest\ORM\Attributes\Lazy;

class Chapter
{
    #[Lazy] public Book $book;

    public string $title;

    public string $contents;
}
