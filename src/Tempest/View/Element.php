<?php

namespace Tempest\View;

interface Element
{
    public function render(ViewRenderer $renderer): string;

    public function previous(): ?Element;

    public function getAttributes(): array;

    public function getAttribute(string $name): ?string;
}