<?php

declare(strict_types=1);

namespace App\Modules\Books\Models;

use Tempest\Interfaces\Model;
use Tempest\ORM\BaseModel;

class Book implements Model
{
    use BaseModel;

    public function __construct(
        public string $title,

        public ?Author $author = null,

        /** @var \App\Modules\Books\Models\Chapter[] */
        public array $chapters = [],
    ) {
    }
}
