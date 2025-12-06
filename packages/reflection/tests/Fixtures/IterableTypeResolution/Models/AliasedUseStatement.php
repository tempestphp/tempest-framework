<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Models;

use Tempest\Reflection\Tests\Fixtures\IterableTypeResolution\Book as MyBook;

final class AliasedUseStatement
{
    /** @var MyBook[] */
    public array $books = [];
}
