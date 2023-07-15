<?php

declare(strict_types=1);

namespace App\Modules\Books;

use Tempest\Interfaces\Model;
use Tempest\ORM\Attributes\Lazy;
use Tempest\ORM\BaseModel;

class Book implements Model
{
    use BaseModel;

    public string $title;

    public Author $author;

    /** @var Chapter[] */
    #[Lazy] public array $chapters;
}
