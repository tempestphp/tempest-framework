<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class CollectionElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly array $elements,
    ) {
    }

    /** @return \Tempest\View\Element[] */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function compile(): string
    {
        $compiled = [];

        foreach ($this->elements as $element) {
            $compiled[] = $element->compile();
        }

        return implode(PHP_EOL, $compiled);
    }
}
