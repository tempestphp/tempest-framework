<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\ViewRenderer;

final class CollectionElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly array $elements,
    ) {}

    /** @return \Tempest\View\Element[] */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function render(ViewRenderer $renderer): string
    {
        $rendered = [];

        foreach ($this->elements as $element) {
            $rendered[] = $element->render($renderer);
        }

        return implode(PHP_EOL, $rendered);
    }
}