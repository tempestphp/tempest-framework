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

    public function setView(View $view): self
    {
        $this->view = $view;

        foreach ($this->children as $child) {
            $child->setView($view);
        }

        return $this;
    }

    public function getAttributes(): array
    {
        return [];
    }

    public function hasAttribute(string $name): bool
    {
        return false;
    }

    public function getAttribute(string $name): string|null
    {
        return null;
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
