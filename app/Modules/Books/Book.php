<?php

namespace App\Modules\Books;

use Tempest\Interfaces\Entity;
use Tempest\ORM\Attributes\Lazy;

class Book implements Entity
{
    public string $title;

    public Author $author;

    /** @var Chapter[] */
    #[Lazy] public array $chapters;
}