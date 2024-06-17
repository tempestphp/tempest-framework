<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\HasAttributes;
use Tempest\View\ViewRenderer;

final class GenericElement implements Element, HasAttributes
{
    use IsElement;

    public function __construct(
        private readonly string $tag,
        private readonly array $attributes,
    ) {
    }

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

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name): ?string
    {
        return $this->attributes[$name] ?? null;
    }
}