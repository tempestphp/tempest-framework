<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;

trait IsElement
{
    public function __construct(
        private readonly ?Element $previous,
        private readonly array $attributes,
        private array $data = [],
    ) {}

    public function data(...$data): self
    {
        $this->data = [...$this->data, ...$data];

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getPrevious(): ?Element
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

    public function debug(int $level = 1): string
    {
        $children = [];

        if ($this instanceof GenericElement) {
            foreach ($this->getChildren() as $child)
            {
                $children[] = str_repeat(' ', $level * 4) . $child->debug($level + 1);
            }
        } elseif ($this instanceof CollectionElement) {
            foreach ($this->elements as $child)
            {
                $children[] = str_repeat(' ', $level * 4) . $child->debug($level + 1);
            }
        }

        $children = implode(PHP_EOL, $children);

        return self::class . ' ' . json_encode($this->data) . PHP_EOL . $children;
    }
}