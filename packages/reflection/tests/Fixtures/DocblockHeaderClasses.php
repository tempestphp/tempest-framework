<?php

declare(strict_types=1);

namespace DocblockHeaderFirst;

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget as Book;

final class First
{
    /** @var Book[] */
    public array $items;
}

namespace DocblockHeaderSecond;

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockGroupTargetB as Book;

final class Second
{
    /** @var Book[] */
    public array $items;
}
