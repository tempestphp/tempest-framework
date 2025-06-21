<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Pattern;

use Tempest\Internationalization\MessageFormat\Parser\Node\Node;

final readonly class Text implements Node
{
    public function __construct(
        public string $value,
    ) {}
}
