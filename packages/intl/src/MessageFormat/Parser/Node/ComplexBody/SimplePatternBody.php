<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\ComplexBody;

use Tempest\Intl\MessageFormat\Parser\Node\Pattern\Pattern;

final readonly class SimplePatternBody implements ComplexBody
{
    public function __construct(
        public Pattern $pattern,
    ) {}

    public function getPattern(): Pattern
    {
        return $this->pattern;
    }
}
