<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures\Imports;

const DOCBLOCK_IMPORT_VERSION = 1;

function docblock_import_helper(): string
{
    return 'helper';
}

final class DocblockImportTarget {}

final class DocblockGroupTargetA {}

final class DocblockGroupTargetB {}
