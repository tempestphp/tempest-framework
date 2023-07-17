<?php

declare(strict_types=1);

namespace App\Modules\Books;

use Tempest\Interfaces\Model;
use Tempest\ORM\BaseModel;

class Author implements Model
{
    use BaseModel;

    public function __construct(
        public string $name,

        /** @var \App\Modules\Books\Book[] */
        public array $books = [],
    ) {}
}
