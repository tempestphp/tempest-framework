<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

final class GenericElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly View $view,
        private readonly string $tag,
        private readonly array $attributes,
    ) {}

    public function render(ViewRenderer $renderer): string
    {
        $content = [];

        foreach ($this->getChildren() as $child) {
            $content[] = $child->render($renderer);
        }

        $content = implode('', $content);

        $attributes = [];

        foreach ($this->attributes as $name => $value) {
            if ($value) {
                $attributes[] = $name . '="' . $value . '"';
            } else {
                $attributes[] = $name;
            }
        }

        $attributes = implode(' ', $attributes);

        if ($attributes !== '') {
            $attributes = ' ' . $attributes;
        }

        return "<{$this->tag}{$attributes}>{$content}</{$this->tag}>";
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name): ?string
    {
        $name = ltrim($name, ':');

        foreach ($this->attributes as $attributeName => $value) {
            if ($attributeName === $name) {
                return $value;
            }

            if ($attributeName === ":{$name}") {
                if (! $value) {
                    return '';
                }

                // TODO: possible refactor with TextElement:25-29 ?
                if (str_starts_with($value, '$this->')) {
                    return $this->view->eval($value) ?? '';
                }

                return $this->getData()[ltrim($value, '$')] ?? '';
            }
        }

        return null;
    }

    public function getSlot(string $name = 'slot'): ?Element
    {
        foreach ($this->getChildren() as $child) {
            if (! $child instanceof SlotElement) {
                continue;
            }

            if ($child->matches($name)) {
                return $child;
            }
        }

        if ($name === 'slot') {
            $elements = [];

            foreach ($this->getChildren() as $child) {
                if ($child instanceof SlotElement) {
                    continue;
                }

                $elements[] = $child;
            }

            return new CollectionElement($elements);
        }

        return null;
    }
}