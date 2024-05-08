<?php

declare(strict_types=1);

namespace App\Modules\Books\Models;

use Tempest\Database\IsModel;
use Tempest\Database\Model;

class Author implements Model
{
    use IsModel;

    public function __construct(
        public string $name,

        /** @var \App\Modules\Books\Models\Book[] */
        public array $books = [],
    ) {
    }
}
