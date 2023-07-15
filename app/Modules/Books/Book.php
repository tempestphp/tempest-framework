<?php

declare(strict_types=1);

namespace App\Modules\Books;

use Tempest\Interfaces\Model;
use Tempest\ORM\Attributes\Lazy;
use Tempest\ORM\BaseModel;

class Book implements Model
{
    use BaseModel;

    /** @var Chapter[] */
    #[Lazy] public array $chapters;

    #[Lazy] public Author $author;

    public function __construct(
        public string $title,
    ) {
    }

    public function setAuthor(Author $author): self
    {
        $this->author = $author;

        return $this;
    }
}
