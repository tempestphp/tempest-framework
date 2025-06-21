<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Pattern;

use Tempest\Internationalization\MessageFormat\Parser\Node\Node;

final class Text implements Node
{
    public function __construct(
        public readonly string $value,
    ) {}
}
