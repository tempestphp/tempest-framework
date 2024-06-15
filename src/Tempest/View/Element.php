<?php

namespace Tempest\View;

interface Element
{
    public function render(ViewRenderer $renderer): string;

    public function data(...$data): self;

    public function getData(): array;

    public function getPrevious(): ?Element;

    public function getAttributes(): array;

    public function getAttribute(string $name): ?string;
}