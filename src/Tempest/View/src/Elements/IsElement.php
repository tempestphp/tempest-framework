<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\View;

/** @phpstan-require-implements \Tempest\View\Element */
trait IsElement
{
    private View $view;

    /** @var Element[] */
    private array $children = [];

    private ?Element $parent = null;

    private ?Element $previous = null;

    private array $attributes = [];

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

    public function setAttribute(string $name, string $value): self
    {
        $this->unsetAttribute($name);

        $this->attributes[$name] = $value;

        return $this;
    }

    public function unsetAttribute(string $name): self
    {
        $name = ltrim($name, ':');

        unset($this->attributes[$name]);
        unset($this->attributes[":{$name}"]);

        return $this;
    }

    public function setPrevious(?Element $previous): self
    {
        $this->previous = $previous;

        return $this;
    }

    public function getPrevious(): ?Element
    {
        return $this->previous;
    }

    public function setParent(?Element $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?Element
    {
        return $this->parent;
    }

    /** @return \Tempest\View\Element[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    /** @param \Tempest\View\Element[] $children */
    public function setChildren(array $children): self
    {
        $previous = null;

        foreach ($children as $child) {
            $child
                ->setParent($this)
                ->setPrevious($previous);

            $previous = $child;
        }

        $this->children = $children;

        return $this;
    }
}
