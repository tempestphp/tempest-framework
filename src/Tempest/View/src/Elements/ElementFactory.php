<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use DOMAttr;
use DOMElement;
use DOMNode;
use DOMText;
use Tempest\Container\Container;
use Tempest\View\Element;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;

final class ElementFactory
{
    public function __construct(
        private ViewConfig $viewConfig,
        private Container $container,
    ) {
    }

    public function make(DOMElement $node): ?Element
    {
        return $this->makeElement(
            node: $node,
            parent: null,
        );
    }

    private function makeElement(DOMNode $node, ?Element $parent): ?Element
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

        if ($viewComponentClass = $this->viewConfig->viewComponents[$node->tagName] ?? null) {
            if (! $viewComponentClass instanceof ViewComponent) {
                $viewComponentClass = $this->container->get($viewComponentClass);
            }

            $attributes = [];

            /** @var DOMAttr $attribute */
            foreach ($node->attributes as $attribute) {
                $name = (string)\Tempest\Support\str($attribute->name)->camel();

                $attributes[$name] = $attribute->value;
            }

            $element = new ViewComponentElement($viewComponentClass, $attributes);
        } elseif ($node->tagName === 'x-slot') {
            $element = new SlotElement(
                name: $node->getAttribute('name') ?: 'slot',
            );
        } else {
            $attributes = [];

            /** @var DOMAttr $attribute */
            foreach ($node->attributes as $attribute) {
                $name = (string)\Tempest\Support\str($attribute->name)->camel();

                $attributes[$name] = $attribute->value;
            }

            $element = new GenericElement(
                tag: $node->tagName,
                attributes: $attributes,
            );
        }

        $children = [];

        foreach ($node->childNodes as $child) {
            $childElement = $this->clone()->makeElement(
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
