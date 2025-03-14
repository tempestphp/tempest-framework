<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\PhpDataElement;
use Tempest\View\Elements\TextElement;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\Exceptions\InvalidDataAttribute;
use Tempest\View\Renderers\TempestViewCompiler;

use function Tempest\Support\str;

final readonly class DataAttribute implements Attribute
{
    public function __construct(
        private string $name,
    ) {
    }

    public function apply(Element $element): Element
    {
        $value = str($element->getAttribute($this->name));

        $value = new TextElement($value->toString())->compile();

        $element->setAttribute($this->name, $value);

        // Data attributes should only be parsed for view components
        if ($element->unwrap(ViewComponentElement::class) === null) {
            return $element;
        }

        $value = $element->getAttribute($this->name);

        if (str($value)->startsWith(TempestViewCompiler::TOKEN_MAPPING)) {
            throw new InvalidDataAttribute($this->name, $value);
        }

        return new PhpDataElement(
            name: $this->name,
            value: $element->getAttribute($this->name),
            wrappingElement: $element,
        );
    }
}
