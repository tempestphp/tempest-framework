<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class SlotElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $name,
    ) {
    }

    public function matches(string $name): bool
    {
        return $this->name === $name;
    }

    public function compile(): string
    {
        $rendered = [];

        foreach ($this->getChildren() as $child) {
            $rendered[] = $child->compile();
        }

        return implode(PHP_EOL, $rendered);
    }
}
