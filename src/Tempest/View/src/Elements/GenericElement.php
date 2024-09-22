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
    ) {}

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

                return $this->eval($value)
                    ?? $this->getData()[ltrim($value, '$')]
                    ?? '';
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
        if ($key && $this->hasAttribute($key)) {
            return $this->getAttribute($key);
        }

        $parentData = $this->getParent()?->getData() ?? [];

        $data = [...$this->view->getData(), ...$parentData, ...$this->data];

        if ($key) {
            return $data[$key] ?? null;
        }

        return $data;
    }

    private function eval(string $eval): mixed
    {
        $data = $this->getData();

        extract($data, flags: EXTR_SKIP);

        /** @phpstan-ignore-next-line */
        return eval("return {$eval};");
    }

    public function __get(string $name)
    {
        return $this->getData($name) ?? $this->view->{$name};
    }

    public function __call(string $name, array $arguments)
    {
        return $this->view->{$name}(...$arguments);
    }
}
