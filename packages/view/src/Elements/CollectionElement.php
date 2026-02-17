<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class CollectionElement implements Element
{
    use IsElement;

    /** @param Element[] $elements */
    public function __construct(array $elements)
    {
        $this->setChildren($elements);
    }

    public function compile(): string
    {
        $compiled = [];

        foreach ($this->getChildren() as $element) {
            $compiled[] = $element->compile();
        }

        return implode(PHP_EOL, $compiled);
    }
}
