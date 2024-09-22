<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use DOMAttr;
use DOMElement;
use DOMNode;
use DOMText;
use Tempest\View\Element;
use Tempest\View\View;

final class ElementFactory
{
    public function make(View $view, DOMElement $node): ?Element
    {
        return $this->makeElement(
            view: $view,
            node: $node,
            parent: null,
        );
    }

    private function makeElement(View $view, DOMNode $node, ?Element $parent): ?Element
    {
        if ($node instanceof DOMText) {
            if (trim($node->textContent) === '') {
                return null;
            }

            return new TextElement(
                text: $node->textContent,
            );
        }

        if (
            ! $node instanceof DOMElement
            || $node->tagName === 'pre'
            || $node->tagName === 'code'
        ) {
            return new RawElement($node->ownerDocument->saveHTML($node));
        }

        if ($node->tagName === 'x-slot') {
            $element = new SlotElement(
                name: $node->getAttribute('name') ?: 'slot',
            );
        } else {
            $attributes = [];

            /** @var DOMAttr $attribute */
            foreach ($node->attributes as $attribute) {
                $name = (string) \Tempest\Support\str($attribute->name)->camel();

                $attributes[$name] = $attribute->value;
            }

            $element = new GenericElement(
                view: $view,
                tag: $node->tagName,
                attributes: $attributes,
            );
        }

        $children = [];

        foreach ($node->childNodes as $child) {
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

        $element->setChildren($children);

        return $element;
    }

    private function clone(): self
    {
        return clone $this;
    }
}
