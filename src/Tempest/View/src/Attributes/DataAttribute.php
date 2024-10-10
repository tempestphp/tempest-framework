<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\PhpDataElement;
use Tempest\View\Elements\ViewComponentElement;
use function Tempest\Support\str;

final readonly class DataAttribute implements Attribute
{
    public function __construct(
        private string $name,
    ) {
    }

    public function apply(Element $element): Element
    {
        if (! $element instanceof ViewComponentElement) {
            return $element;
        }

        $value = str($element->getAttribute($this->name));

        return new PhpDataElement(
            $this->name,
            $value->toString(),
            $element,
        );
    }
}
