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

    public function addBooks(Book ...$books): self
    {
        $this->books ??= [];

        foreach ($books as $book) {
            $book->setAuthor($this);
            $this->books[] = $book;
        }

        return $this;
    }
}
