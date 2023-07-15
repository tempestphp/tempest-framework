<?php

declare(strict_types=1);

namespace App\Modules\Books;

use Tempest\Interfaces\Model;
use Tempest\ORM\Attributes\Lazy;
use Tempest\ORM\BaseModel;

class Author implements Model
{
    use BaseModel;

    public string $name;

    /** @var Book[] */
    #[Lazy] public array $books;
}
