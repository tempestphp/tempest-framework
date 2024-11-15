<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use DOMAttr;
use DOMElement;
use DOMNode;
use DOMText;
use Tempest\Container\Container;
use function Tempest\Support\str;
use Tempest\View\Element;
use Tempest\View\Renderers\TempestViewCompiler;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;

final class ElementFactory
{
    private TempestViewCompiler $compiler;

    public function __construct(
        private readonly ViewConfig $viewConfig,
        private readonly Container $container,
    ) {
    }

    public function setViewCompiler(TempestViewCompiler $compiler): self
    {
        $this->compiler = $compiler;

        return $this;
    }

    public function make(DOMNode $node): ?Element
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

        $attributes = [];

        /** @var DOMAttr $attribute */
        foreach ($node->attributes ?? [] as $attribute) {
            $name = str($attribute->name)->camel()->toString();

            $attributes[$name] = $attribute->value;
        }

        if (! $node instanceof DOMElement
            || $node->tagName === 'pre'
            || $node->tagName === 'code'
            || $node->tagName === 'x-raw'
        ) {
            $content = '';

            foreach ($node->childNodes as $child) {
                $content .= $node->ownerDocument->saveHTML($child);
            }

            return new RawElement(
                tag: $node->tagName ?? null,
                content: $content,
                attributes: $attributes,
            );
        }

        if ($viewComponentClass = $this->viewConfig->viewComponents[$node->tagName] ?? null) {
            if (! $viewComponentClass instanceof ViewComponent) {
                $viewComponentClass = $this->container->get($viewComponentClass);
            }

            $element = new ViewComponentElement(
                compiler: $this->compiler,
                viewComponent: $viewComponentClass,
                attributes: $attributes,
            );
        } elseif ($node->tagName === 'x-slot') {
            $element = new SlotElement(
                name: $node->getAttribute('name') ?: 'slot',
            );
        } else {
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
