<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\View;

final class GenericElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly View $view,
        private readonly string $tag,
        private readonly array $attributes,
    ) {
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function hasAttribute(string $name): bool
    {
        $name = ltrim($name, ':');

        return array_key_exists($name, $this->attributes)
            || array_key_exists(":{$name}", $this->attributes);
    }

    public function getAttribute(string $name, bool $eval = true): mixed
    {
        $name = ltrim($name, ':');

        foreach ($this->attributes as $attributeName => $value) {
            if ($attributeName === $name) {
                return $value;
            }

            if ($attributeName === ":{$name}") {
                if (! $value) {
                    return null;
                }

                if (! $eval) {
                    return $value;
                }

                // TODO: possible refactor with TextElement:25-29 ?
                if (str_starts_with($value, '$this->')) {
                    $result = $this->view->eval($value);

                    if (is_bool($result) || is_string($result)) {
                        return $result;
                    }

                    return (bool) $result;
                }

                return $this->getData()[ltrim($value, '$')] ?? '';
            }
        }

        return null;
    }

    public function getSlot(string $name = 'slot'): ?Element
    {
        foreach ($this->getChildren() as $child) {
            if (! $child instanceof SlotElement) {
                continue;
            }

            if ($child->matches($name)) {
                return $child;
            }
        }

        if ($name === 'slot') {
            $elements = [];

            foreach ($this->getChildren() as $child) {
                if ($child instanceof SlotElement) {
                    continue;
                }

                $elements[] = $child;
            }

            return new CollectionElement($elements);
        }

        return null;
    }

    public function getData(?string $key = null): mixed
    {
        $parentData = $this->getParent()?->getData() ?? [];

        $data = [...$this->view->getData(), ...$parentData, ...$this->data];

        if ($key) {
            return $data[$key] ?? null;
        }

        return $data;
    }
}
