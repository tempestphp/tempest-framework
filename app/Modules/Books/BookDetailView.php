<?php

declare(strict_types=1);

namespace App\Modules\Books;

use App\Modules\Books\Models\Book;
use Tempest\Interfaces\View;
use Tempest\View\BaseView;

final class BookDetailView implements View
{
    use BaseView;

    public function __construct(
        public Book $book,
    ) {
        $this
            ->path('/Modules/Books/book.view.php')
            ->extends('/Views/base.php', title: $this->book->title);
    }
}
