<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\View\Elements\SlotElement;

final class Slot
{
    public function __construct(
        public string $name,
        public array $attributes,
        public string $content,
    ) {
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public static function fromElement(SlotElement $element): self
    {
        return new self(
            name: $element->name,
            attributes: $element->getAttributes(),
            content: $element->compile(),
        );
    }

    public static function __set_state(array $array): object
    {
        return new self(...$array);
    }
}
