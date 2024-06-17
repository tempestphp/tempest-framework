<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\ViewRenderer;

final class SlotElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $name,
    ) {}

    public function matches(string $name): bool
    {
        return $this->name === $name;
    }

    public function render(ViewRenderer $renderer): string
    {
        $rendered = [];

        foreach ($this->getChildren() as $child) {
            $rendered[] = $child->render($renderer);
        }

        return implode(PHP_EOL, $rendered);
    }
}