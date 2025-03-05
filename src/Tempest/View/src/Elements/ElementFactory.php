<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Dom\Comment;
use Dom\DocumentType;
use Dom\Element as DomElement;
use Dom\Node;
use Dom\Text;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\View\Element;
use Tempest\View\Renderers\TempestViewCompiler;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;
use function Tempest\Support\str;

final class ElementFactory
{
    private TempestViewCompiler $compiler;

    public function __construct(
        private readonly AppConfig $appConfig,
        private readonly ViewConfig $viewConfig,
        private readonly Container $container,
    ) {}

    public function setViewCompiler(TempestViewCompiler $compiler): self
    {
        $this->compiler = $compiler;

        return $this;
    }

    public function make(Node $node): ?Element
    {
        return $this->makeElement(
            node: $node,
            parent: null,
        );
    }

    private function makeElement(Node $node, ?Element $parent): ?Element
    {
        if ($node instanceof DocumentType) {
            $content = $node->ownerDocument->saveHTML($node);

            return new RawElement(tag: null, content: $content);
        }

        if ($node instanceof Text) {
            if (trim($node->textContent) === '') {
                return null;
            }

            return new TextElement(
                text: $node->textContent,
            );
        }

        if ($node instanceof Comment) {
            return new CommentElement(
                content: $node->textContent,
            );
        }

        $tagName = strtolower($node->tagName);

        $attributes = [];

        /** @var \Dom\Attr $attribute */
        foreach ($node->attributes ?? [] as $attribute) {
            $name = str($attribute->name)->camel()->toString();

            $attributes[$name] = $attribute->value;
        }

        if (! $node instanceof DomElement
            || $tagName === 'pre'
            || $tagName === 'code') {
            $content = '';

            foreach ($node->childNodes as $child) {
                $content .= $node->ownerDocument->saveHTML($child);
            }

            return new RawElement(
                tag: $tagName,
                content: $content,
                attributes: $attributes,
            );
        }

        if ($viewComponentClass = $this->viewConfig->viewComponents[$tagName] ?? null) {
            if (! $viewComponentClass instanceof ViewComponent) {
                $viewComponentClass = $this->container->get($viewComponentClass);
            }

            $element = new ViewComponentElement(
                environment: $this->appConfig->environment,
                compiler: $this->compiler,
                viewComponent: $viewComponentClass,
                attributes: $attributes,
            );
        } elseif ($tagName === 'x-template') {
            $element = new TemplateElement(
                attributes: $attributes,
            );
        } elseif ($tagName === 'x-slot') {
            $element = new SlotElement(
                name: $node->getAttribute('name') ?: 'slot',
                attributes: $attributes,
            );
        } else {
            $element = new GenericElement(
                tag: $tagName,
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
