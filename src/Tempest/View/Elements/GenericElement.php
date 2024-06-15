<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\ViewRenderer;

final readonly class GenericElement implements Element
{
    use IsElement;

    public function __construct(
        private string $html,
        private string $tag,
        /** @var \Tempest\View\Element[] */
        private array $children,
        private ?Element $previous,
        private array $attributes,
    ) {}

    public function render(ViewRenderer $renderer): string
    {
        $content = [];

        foreach ($this->children as $child) {
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

    public function getChildren(): array
    {
        return $this->children;
    }
}