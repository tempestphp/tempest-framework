<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures;

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget;

// A top-level closure `use` must not be parsed as an import statement.
$docblockClosureUseVar = 'value';
$docblockClosureUseFactory = function (string $name) use ($docblockClosureUseVar): string { // @mago-ignore lint:prefer-arrow-function
    return $name . $docblockClosureUseVar;
};

final class DocblockClosureUseModel
{
    /** @var DocblockImportTarget[] */
    public array $items = [];
}
