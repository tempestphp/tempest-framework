<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class GenericElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $tag,
        private readonly array $attributes,
    ) {
    }

    public function compile(): string
    {
        $content = [];

        foreach ($this->getChildren() as $child) {
            $content[] = $child->compile();
        }

        $content = implode('', $content);

        $attributes = [];

        foreach ($this->getAttributes() as $name => $value) {
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

    public function hasAttribute(string $name): bool
    {
        $name = ltrim($name, ':');

        return
            array_key_exists(":{$name}", $this->attributes) ||
            array_key_exists($name, $this->attributes);
    }

    public function getAttribute(string $name): string|null
    {
        $name = ltrim($name, ':');

        return $this->attributes[":{$name}"]
            ?? $this->attributes[$name]
            ?? null;
    }
}
