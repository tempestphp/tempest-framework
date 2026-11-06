<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures;

// Mixed import context: `function`/`const` statements alongside class imports
// must not register aliases, and class members must still resolve.
use Tempest\Reflection\Tests\Fixtures\Imports\DocblockGroupTargetA as MixedGroupTarget;
use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget;

// @mago-ignore lint:no-redundant-use
use function Tempest\Reflection\Tests\Fixtures\Imports\docblock_import_helper;

// @mago-ignore lint:no-redundant-use
use const Tempest\Reflection\Tests\Fixtures\Imports\DOCBLOCK_IMPORT_VERSION;

final class DocblockMixedGroupModel
{
    /** @var MixedGroupTarget[] */
    public array $items = [];

    /** @var DocblockImportTarget[] */
    public array $shadowedByFunction = [];
}
