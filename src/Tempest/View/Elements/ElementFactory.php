<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use PHPHtmlParser\Dom\AbstractNode;
use PHPHtmlParser\Dom\InnerNode;
use Tempest\View\Element;
use Tempest\View\View;

final class ElementFactory
{
    public function make(View $view, AbstractNode $node): ?Element
    {
        //        $attributes = $this->attributeFactory->makeCollection($view, $node);
        //
        //        foreach ($attributes as $attribute) {
        //            $element = $attribute->apply($element);
        //        }

        return $this->makeElement(
            view: $view,
            node: $node,
            parent: null,
        );
    }

    private function makeElement(View $view, AbstractNode $node, ?Element $parent): ?Element
    {
        if ($node->getTag()->name() === 'text') {
            if (trim($node->outerHtml()) === '') {
                return null;
            }

            return new TextElement(
                text: $node->outerHtml(),
            );
        }

        if ($node->getTag()->name() === 'x-slot') {
            $element = new SlotElement(
                name: $node->getAttribute('name') ?? 'slot',
            );
        } else {
            $element = new GenericElement(
                view: $view,
                tag: $node->getTag()->name(),
                attributes: $node->getAttributes(),
            );
        }

        $children = [];

        if ($node instanceof InnerNode) {
            foreach ($node->getChildren() as $child) {
                $childElement = $this->clone()->makeElement(
                    view: $view,
                    node: $child,
                    parent: $parent,
                );

                if ($childElement === null) {
                    continue;
                }

                $children[] = $childElement;
            }
        }

        $element->setChildren($children);

        return $element;
    }

    private function clone(): self
    {
        return clone $this;
    }
}
