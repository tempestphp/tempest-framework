<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Tag;

final class DependencyWithBuiltinDependencies
{
    public function __construct(
        #[Tag('builtin-dependency-array')]
        public readonly array $arrayValue,
        #[Tag('builtin-dependency-string')]
        public readonly string $stringValue,
        #[Tag('builtin-dependency-bool')]
        public readonly bool $boolValue,
    ) {
    }
}
