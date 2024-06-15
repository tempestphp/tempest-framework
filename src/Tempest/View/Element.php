<?php

namespace Tempest\View;

final class Element
{
    public function __construct(
        private string $html,

        private string $tag,

        /** @var string[] */
        private array $attributes = [],

        /** @var \Tempest\View\Element[] */
        private array $children = [],
    ) {}

    public function render(): string|View
    {
        $content = [];

        foreach ($this->children as $child) {
            $content[] = $child->render();
        }

        $content = implode('', $content);

        return "<{$this->tag}>{$content}</{$this->tag}>";
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getChildren(): array
    {
        return $this->children;
    }
}