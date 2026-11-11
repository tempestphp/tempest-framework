<?php

declare(strict_types=1);

namespace DocblockTraitSource;

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget as Book;

trait Items
{
    /** @var Book[] */
    public array $items;
}

trait NestedItems
{
    use Items;
}

trait ReadonlyItems
{
    /** @var Book[] */
    public readonly array $items;
}

namespace DocblockTraitConsumer;

use Tempest\Reflection\Tests\Fixtures\Imports\DocblockGroupTargetB as Book;

class Holder
{
    use \DocblockTraitSource\NestedItems;

    public function accept(array $items): void {}
}

final class Child extends Holder {}

final class Override
{
    use \DocblockTraitSource\Items;

    /** @var Book[] */
    public array $items;
}

final class PromotedOverride
{
    use \DocblockTraitSource\Items;

    public function __construct(
        /** @var Book[] */
        public array $items,
    ) {}
}

final class ReadonlyPromotedOverride
{
    use \DocblockTraitSource\ReadonlyItems;

    public function __construct(
        /** @var Book[] */
        readonly array $items,
    ) {}
}
