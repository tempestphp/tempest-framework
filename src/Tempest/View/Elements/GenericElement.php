<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\ViewRenderer;

final class GenericElement implements Element
{
    use IsElement;

    /** @var \Tempest\View\Element[] */
    private array $children = [];

    public function __construct(
        private readonly string $html,
        private readonly string $tag,
        private readonly ?Element $previous,
        private readonly array $attributes,
        private array $data = [],
    ) {}

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

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

    public function data(...$data): self
    {
        $this->data = [...$this->data, ...$data];

        foreach ($this->children as $child) {
            $child->data(...$data);
        }

        return $this;
    }

    public function __clone(): void
    {
        $childClones = [];

        foreach ($this->children as $child) {
            $childClones[] = clone $child;
        }

        $this->children = $childClones;
    }
}