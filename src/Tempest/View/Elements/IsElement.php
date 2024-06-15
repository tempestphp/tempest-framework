<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;

trait IsElement
{
    public function __construct(
        private readonly ?Element $previous,
        private readonly array $attributes,
    ) {}

    public function previous(): ?Element
    {
        return $this->previous;
    }

    public function getAttribute(string $name): ?string
    {
        return $this->attributes[$name] ?? null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}