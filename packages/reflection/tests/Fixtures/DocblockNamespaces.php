<?php

declare(strict_types=1);

namespace DocblockBracketed {
    use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget as Book;

    final class First
    {
        /** @var Book[] */
        public array $items;
    }
}

namespace DocblockOtherBracketed {
    use Tempest\Reflection\Tests\Fixtures\Imports\DocblockGroupTargetB as Book;

    final class Second
    {
        /** @var Book[] */
        public array $items;
    }
}

namespace {
    use Tempest\Reflection\Tests\Fixtures\Imports\DocblockGroupTargetA as Book;

    final class DocblockGlobalHolder
    {
        /** @var Book[] */
        public array $items;
    }
}
