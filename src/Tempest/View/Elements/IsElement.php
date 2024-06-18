<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;

trait IsElement
{
    /** @var Element[] */
    private array $children = [];

    private ?Element $parent = null;

    private ?Element $previous = null;

    private array $data = [];

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

    public function getData(?string $key = null): mixed
    {
        $parentData = $this->getParent()?->getData() ?? [];

        $data = [...$parentData, ...$this->data];

        if ($key) {
            return $data[$key] ?? null;
        }

        return $data;
    }

    public function addData(...$data): self
    {
        $this->data = [...$this->data, ...$data];

        return $this;
    }

    public function __clone(): void
    {
        $childClones = [];

        foreach ($this->children as $child) {
            $childClones[] = clone $child;
        }

        $this->setChildren($childClones);
    }
}