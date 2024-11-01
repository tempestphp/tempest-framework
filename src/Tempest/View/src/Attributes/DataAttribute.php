<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use function Tempest\Support\str;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\PhpDataElement;
use Tempest\View\Elements\TextElement;
use Tempest\View\Elements\ViewComponentElement;

final readonly class DataAttribute implements Attribute
{
    public function __construct(
        private string $name,
    ) {
    }

    public function apply(Element $element): Element
    {
        $value = str($element->getAttribute($this->name));

        // Render {{ and {!! tags
        if ($value->startsWith(['{{', '{!!']) && $value->endsWith(['}}', '!!}'])) {
            $value = (new TextElement($value->toString()))->compile();
            $element->setAttribute($this->name, $value);
        }

        // Data should only be defined for view component elements and data elements,
        // otherwise it's a plain HTML attribute
        if (
            ! $element instanceof ViewComponentElement // TODO: unwrap
            && ! $element instanceof PhpDataElement
        ) {
            return $element;
        }

        return new PhpDataElement(
            name: $this->name,
            value: $element->getAttribute($this->name),
            wrappingElement: $element,
        );
    }
}
