<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures;

$example = <<<'PHP'
use Incorrect\DocblockImportTarget;
PHP;

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget;

final class DocblockStringImport
{
    /** @var DocblockImportTarget[] */
    public array $items;
}
