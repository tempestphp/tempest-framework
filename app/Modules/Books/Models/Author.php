<?php

declare(strict_types=1);

namespace App\Modules\Books\Models;

use Tempest\Interfaces\Model;
use Tempest\ORM\BaseModel;

class Author implements Model
{
    use BaseModel;

    public function __construct(
        public string $name,

        /** @var \App\Modules\Books\Models\Book[] */
        public array $books = [],
    ) {
    }
}
