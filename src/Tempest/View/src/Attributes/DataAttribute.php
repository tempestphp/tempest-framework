<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;

final readonly class DataAttribute implements Attribute
{
    public function __construct(
        private string $name,
    ) {}

    public function apply(Element $element): Element
    {
        $value = $element->getAttribute($this->name);

        return $element->setAttribute(
            $this->name,
            sprintf('<?= %s ?>', $value)
        );
    }
}
