<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Models;

use Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Author;
use Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Book;

final class GroupUseStatement
{
    /** @var Book[] */
    public array $books = [];

    /** @var Author[] */
    public array $authors = [];
}
