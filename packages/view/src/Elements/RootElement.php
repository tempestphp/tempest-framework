<?php
declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class RootElement implements Element
{
    use IsElement;

    private array $inheritedImports = [];

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

        $this->mergeImports($imports, $this->inheritedImports);

        foreach ($this->children as $child) {
            if ($child instanceof PhpElement) {
                $this->mergeImports($imports, $child->getImports());
            }
        }

        return array_values($imports);
    }

    public function setInheritedImports(array $imports): self
    {
        $this->inheritedImports = $imports;

        return $this;
    }

    private function mergeImports(array &$imports, array $candidates): void
    {
        foreach ($candidates as $import) {
            $import = trim($import);

            if ($import === '') {
                continue;
            }

            $imports[$import] = $import;
        }
    }
}
