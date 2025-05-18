<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\IsDatabaseModel;
use Tempest\Router\Bindable;

final class Author implements Bindable
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
        public ?AuthorType $type = AuthorType::A,

        /** @var \Tests\Tempest\Fixtures\Modules\Books\Models\Book[] */
        public array $books = [],
        public ?Publisher $publisher = null,
    ) {}
}
