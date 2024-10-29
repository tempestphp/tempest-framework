<?php

declare(strict_types=1);

namespace Tempest\View;

interface Element
{
    public function compile(): string;

    public function getAttributes(): array;

    public function hasAttribute(string $name): bool;

    public function getAttribute(string $name): string|null;

    public function setAttribute(string $name, string $value): self;

    public function consumeAttribute(string $name): string|null;

    public function setPrevious(?Element $previous): self;

    public function getPrevious(): ?Element;

    public function setParent(?Element $parent): self;

    public function getParent(): ?Element;

    /** @param \Tempest\View\Element[] $children */
    public function setChildren(array $children): self;

    /** @return \Tempest\View\Element[] */
    public function getChildren(): array;
}
