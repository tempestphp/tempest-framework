<?php

namespace Tempest\View;

interface HasAttributes
{
    /** @return array<string, string> */
    public function getAttributes(): array;

    public function getAttribute(string $name): ?string;
}