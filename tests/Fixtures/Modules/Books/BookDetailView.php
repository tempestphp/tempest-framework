<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books;

use Tempest\View\IsView;
use Tempest\View\View;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;

final class BookDetailView implements View
{
    use IsView;

    public function __construct(
        public Book $book,
    ) {
        $this
            ->path('/Modules/Books/book.view.php')
            ->extends('/Views/base.php', title: $this->book->title);
    }
}
