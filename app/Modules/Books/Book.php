<?php

declare(strict_types=1);

namespace App\Modules\Books;

use Tempest\Interfaces\Model;
use Tempest\ORM\BaseModel;

class Book implements Model
{
    use BaseModel;

    public function __construct(
        public string $title,

        public ?Author $author = null,

        /** @var Chapter[] */
        public array $chapters = [],
    ) {}
}
