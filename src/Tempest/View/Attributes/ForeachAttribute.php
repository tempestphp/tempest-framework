<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\CollectionElement;
use Tempest\View\View;

final readonly class ForeachAttribute implements Attribute
{
    public function __construct(
        private View $view,
        private string $eval,
    ) {
    }

    public function apply(Element $element): Element
    {
        preg_match(
            '/\$this->(?<collection>\w+) as \$(?<item>\w+)/',
            $this->eval,
            $matches,
        );

        $collection = $this->view->get($matches['collection']);
        $itemName = $matches['item'];

        $elements = [];

        foreach ($collection as $item) {
            $elementClone = clone $element;

            $elements[] = $elementClone->addData(...[$itemName => $item]);
        }

        return new CollectionElement(
            elements: $elements,
        );
    }
}
