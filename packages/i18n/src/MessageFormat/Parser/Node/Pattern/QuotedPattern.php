<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Pattern;

use Tempest\Internationalization\MessageFormat\Parser\Node\ComplexBody\ComplexBody;

final readonly class QuotedPattern implements ComplexBody, Placeholder
{
    public function __construct(
        public Pattern $pattern,
    ) {}

    public function getPattern(): Pattern
    {
        return $this->pattern;
    }
}
