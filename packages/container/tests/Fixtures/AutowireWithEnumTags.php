<?php

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Tag;

final class AutowireWithEnumTags
{
    public function __construct(
        #[Tag(EnumTag::FOO)]
        public TaggedDependency $foo,
        #[Tag(EnumTag::BAR)]
        public TaggedDependency $bar,
    ) {}
}
