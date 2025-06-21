<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\ComplexBody;

use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\Pattern;

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
