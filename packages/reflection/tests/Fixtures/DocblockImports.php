<?php

declare(strict_types=1);

// @mago-format-ignore: import spelling, grouping and placement are part of the parser fixture.

namespace DocblockImports;

// @mago-ignore lint:lowercase-keyword
use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget AS Book;

final class UppercaseAlias
{
    /** @var Book[] */
    public array $items;
}

final class LowercaseReference
{
    /** @var book[] */
    public array $items;
}

use Tempest\Reflection\Tests\Fixtures\{Imports\DocblockImportTarget as GroupBook, Imports as Children};
use Tempest\Reflection\Tests\Fixtures\Imports\{DocblockGroupTargetB as MixedBook, function docblock_import_helper as Book, const DOCBLOCK_IMPORT_VERSION as MixedBook};

final class Grouped
{
    /** @var GroupBook[] */
    public array $items;
}

final class GroupedPrefix
{
    /** @var Children\DocblockGroupTargetA[] */
    public array $items;
}

final class MixedGrouped
{
    /** @var MixedBook[] */
    public array $items;
}

final class MixedGroupedDoesNotShadowClass
{
    /** @var Book[] */
    public array $items;
}

use /* comment */ function strlen as GroupBook;
use const PHP_VERSION as GroupBook;

$capture = 'value';
$closure = function () use ($capture) { return "{$capture}"; }; // @mago-ignore lint:prefer-arrow-function

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockGroupTargetA as After;

final class AfterClosure
{
    /** @var After[] */
    public array $items;
}

final class NamespaceBook {}

final class NamespaceRelative
{
    // PHPStan does not resolve namespace-relative names in @var tags.
    /**
     * @var namespace\NamespaceBook[]
     * @phpstan-var NamespaceBook[]
     */
    public array $items;
}

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget as FunctionAlias;
// @mago-ignore lint:no-redundant-use
use function strlen as IgnoredFunction, count as FunctionAlias;

final class FunctionList
{
    /** @var FunctionAlias[] */
    public array $items;
}

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockGroupTargetA as ConstantAlias;
// @mago-ignore lint:no-redundant-use
use const PHP_VERSION as IgnoredConstant, PHP_MAJOR_VERSION as ConstantAlias;

final class ConstantList
{
    /** @var ConstantAlias[] */
    public array $items;
}

$literalBrace = "{$capture}{";

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockGroupTargetB as StringTarget;

final class AfterString
{
    /** @var StringTarget[] */
    public array $items;
}

namespace DocblockOtherImports;

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockGroupTargetB as Book;

final class OtherNamespace
{
    /** @var Book[] */
    public array $items;
}
