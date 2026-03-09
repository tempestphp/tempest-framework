<?php

declare(strict_types=1);

namespace Tempest\View;

interface Element
{
    public function compile(): string;

    public function getAttributes(): array;

    public function hasAttribute(string $name): bool;

    public function getAttribute(string $name): ?string;

    public function setAttribute(string $name, string $value): self;

    public function addRawAttribute(string $attribute): self;

    public function consumeAttribute(string $name): ?string;

    public function unsetAttribute(string $name): self;

    public function setPrevious(?Element $previous): self;

    public function getPrevious(): ?Element;

    public function setParent(?Element $parent): self;

    public function getParent(): ?Element;

    /** @param \Tempest\View\Element[] $children */
    public function setChildren(array $children): self;

    /** @return \Tempest\View\Element[] */
    public function getChildren(): array;

    /**
     * @template T of \Tempest\View\Element
     * @param class-string<T> $elementClass
     * @return T|null
     */
    public function unwrap(string $elementClass): ?Element;

    /**
     * @return string[] An array of import statements
     */
    public function getImports(): array;
}
