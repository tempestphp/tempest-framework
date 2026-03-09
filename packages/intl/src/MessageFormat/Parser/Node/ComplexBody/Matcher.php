<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\ComplexBody;

use Tempest\Intl\MessageFormat\Parser\Node\Pattern\Pattern;
use Tempest\Intl\MessageFormat\Parser\Node\Variable;

final readonly class Matcher implements ComplexBody
{
    /**
     * @param Variable[] $selectors
     * @param Variant[] $variants
     */
    public function __construct(
        public array $selectors,
        public array $variants,
    ) {}

    public function getPattern(): Pattern
    {
        $elements = [];

        foreach ($this->variants as $variant) {
            $elements[] = $variant->pattern;
        }

        return new Pattern($elements);
    }
}
