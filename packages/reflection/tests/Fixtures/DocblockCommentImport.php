<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures;

/*
 * use Incorrect\DocblockImportTarget;
 */
use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget;

final class DocblockCommentImport
{
    /** @var DocblockImportTarget[] */
    public array $items;
}
