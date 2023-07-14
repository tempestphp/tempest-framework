<?php

namespace App\Modules\Books;

use Tempest\ORM\Attributes\Lazy;

class Author
{
    public string $name;

    /** @var Book[] */
    #[Lazy] public array $books;
}