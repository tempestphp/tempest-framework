<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Models;

use Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Book;

final class RegularUseStatement
{
    /** @var Book[] */
    public array $books = [];
}
