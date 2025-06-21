<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\ComplexBody;

use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\Pattern;

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
