<?php

declare(strict_types=1);

namespace Tempest\View;

interface Element
{
    public function setPrevious(?Element $previous): self;

    public function getPrevious(): ?Element;

    public function setParent(?Element $parent): self;

    public function getParent(): ?Element;

    /** @param \Tempest\View\Element[] $children */
    public function setChildren(array $children): self;

    /** @return \Tempest\View\Element[] */
    public function getChildren(): array;

    public function getData(?string $key = null): mixed;

    public function addData(...$data): self;
}
