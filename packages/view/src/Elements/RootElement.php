<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class RootElement implements Element
{
    use IsElement;

    public function compile(): string
    {
        $compiled = [];

        foreach ($this->children as $element) {
            $compiled[] = $element->compile();
        }

        return implode($compiled);
    }

    public function getImports(): array
    {
        $imports = [];

        foreach ($this->children as $child) {
            if ($child instanceof PhpElement) {
                $imports = [...$imports, ...$child->getImports()];
            }
        }

        return $imports;
    }
}
