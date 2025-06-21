<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\ComplexBody;

use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\Pattern;

final class SimplePatternBody implements ComplexBody
{
    public function __construct(
        public readonly Pattern $pattern,
    ) {}

    public function getPattern(): Pattern
    {
        return $this->pattern;
    }
}
