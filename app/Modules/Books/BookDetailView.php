<?php

declare(strict_types=1);

namespace App\Modules\Books;

use App\Modules\Books\Models\Book;
use Tempest\View\IsView;
use Tempest\View\View;

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
