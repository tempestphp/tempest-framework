<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\GenericElement;
use Tempest\View\Exceptions\ExpressionAttributeWasInvalid;
use Tempest\View\Parser\TempestViewCompiler;

use function Tempest\Support\str;

final readonly class AsAttribute implements Attribute
{
    public function __construct(
        private string $name,
    ) {}

    public function apply(Element $element): Element
    {
        $value = str($element->consumeAttribute($this->name) ?? '');

        if ($value->isEmpty()) {
            return $element;
        }

        $generic = $element->unwrap(GenericElement::class);

        if (! $generic instanceof Element) {
            return $element;
        }

        // :as="expression" — follows the ExpressionAttribute convention
        if (str($this->name)->startsWith(':')) {
            if ($value->startsWith(['{{', '{!!', ...TempestViewCompiler::PHP_TOKENS])) {
                throw new ExpressionAttributeWasInvalid($value);
            }

            return $generic->withTagExpression($value->toString());
        }

        // as="literal-tag"
        return $generic->withTag($value->toString());
    }
}
