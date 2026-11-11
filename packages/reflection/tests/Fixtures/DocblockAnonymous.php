<?php

declare(strict_types=1);

namespace DocblockAnonymous;

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget as Book;

$className = Book::class;

return new class(
    new class {},
    // @mago-ignore lint:prefer-arrow-function
    (static function () {
        return null;
    })(),
) {
    public function __construct(object $argument, mixed $other)
    {
        assert($argument !== $this, 'The argument is the nested anonymous class instance.');
        assert($other === null, 'The nested closure returns null.');
    }

    /** @var Book[] */
    public array $items;
};
