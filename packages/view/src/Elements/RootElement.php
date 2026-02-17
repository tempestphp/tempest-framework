<?php

namespace Tempest\View\Elements;

use Tempest\View\Attributes\PhpAttribute;
use Tempest\View\Element;
use Tempest\View\HasImports;

final class RootElement implements Element, HasImports
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