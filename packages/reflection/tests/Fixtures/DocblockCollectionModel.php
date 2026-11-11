<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures;

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget;
use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget as DocblockImportTargetAlias;

final class DocblockCollectionModel
{
    /** @var DocblockImportTarget[] */
    public array $imported = [];

    /** @var DocblockImportTargetAlias[] */
    public array $aliased = [];

    /** @var \Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget[] */
    public array $fullyQualified = [];

    /** @var list<DocblockImportTarget> */
    public array $list = [];

    /** @var array<DocblockImportTarget> */
    public array $arrayType = [];

    /** @var DocblockRelativeTarget[] */
    public array $relative = [];

    public array $noDocblock = [];
}
