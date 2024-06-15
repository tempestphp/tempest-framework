<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\ViewRenderer;

final class EmptyElement implements Element
{
    public function __construct(
        private readonly Element $original,
        private array $data = [],
    ) {}

    public function render(ViewRenderer $renderer): string
    {
        return '';
    }

    public function data(...$data): Element
    {
        $this->data = [...$this->data, ...$data];
    }

    public function getData(): array
    {
        return [...$this->original->getData(), ...$this->data];
    }

    public function getPrevious(): ?Element
    {
        return $this->original->getPrevious();
    }

    public function getAttributes(): array
    {
        return $this->original->getAttributes();
    }

    public function getAttribute(string $name): ?string
    {
        return $this->original->getAttribute($name);
    }
}