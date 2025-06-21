<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Pattern;

use Tempest\Internationalization\MessageFormat\Parser\Node\ComplexBody\ComplexBody;

final class QuotedPattern implements ComplexBody, Placeholder
{
    public function __construct(
        public readonly Pattern $pattern,
    ) {}

    public function getPattern(): Pattern
    {
        return $this->pattern;
    }
}
