<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\Pattern;

use Tempest\Intl\MessageFormat\Parser\Node\Node;

final readonly class Text implements Node
{
    public function __construct(
        public string $value,
    ) {}
}
