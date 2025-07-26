<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\PhpDataElement;
use Tempest\View\Elements\TextElement;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\Exceptions\DataAttributeWasInvalid;
use Tempest\View\Parser\TempestViewCompiler;

use function Tempest\Support\str;

final readonly class DataAttribute implements Attribute
{
    public function __construct(
        private string $name,
    ) {}

    public function apply(Element $element): Element
    {
        $value = str($element->getAttribute($this->name));

        $value = new TextElement($value->toString())->compile();

        $element->setAttribute($this->name, $value);

        if (
            $element->unwrap(ViewComponentElement::class)
            && str($value)->startsWith(TempestViewCompiler::PHP_TOKENS)
        ) {
            throw new DataAttributeWasInvalid($this->name, $value);
        }

        return $element;
    }
}
