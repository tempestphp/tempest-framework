<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\ViewRenderer;

final class CollectionElement implements Element
{
    use IsElement;

    public function __construct(
        /** @var \Tempest\View\Element[] */
        private readonly array $elements,
        private readonly ?Element $previous,
        private readonly array $attributes,
        private array $data = [],
    ) {}

    public function render(ViewRenderer $renderer): string
    {
        $rendered = [];

        foreach ($this->elements as $element) {
            $rendered[] = $element->render($renderer);
        }

        return implode(PHP_EOL, $rendered);
    }
}