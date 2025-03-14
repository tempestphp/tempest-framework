<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Stringable;
use Tempest\Support\Arr\ArrayInterface;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\PhpDataElement;
use Tempest\View\Exceptions\InvalidExpressionAttribute;
use Tempest\View\Renderers\TempestViewCompiler;

use function Tempest\Support\str;

final readonly class ExpressionAttribute implements Attribute
{
    public function __construct(
        private string $name,
    ) {
    }

    public function apply(Element $element): Element
    {
        $value = str($element->getAttribute($this->name));

        if ($value->startsWith(['{{', '{!!', ...TempestViewCompiler::TOKEN_MAPPING])) {
            throw new InvalidExpressionAttribute($value);
        }

        return new PhpDataElement(
            name: $this->name,
            value: $value->toString(),
            wrappingElement: $element->setAttribute(
                ltrim($this->name, ':'),
                sprintf('<?= \Tempest\View\Attributes\ExpressionAttribute::toValue(%s); ?>', $value),
            ),
        );
    }

    public static function toValue(mixed $attribute): mixed
    {
        if ($attribute instanceof Stringable) {
            $attribute = (string) $attribute;
        }

        if ($attribute instanceof ArrayInterface) {
            $attribute = $attribute->toArray();
        }

        if (is_array($attribute)) {
            return trim(implode(' ', $attribute));
        }

        return (string) $attribute;
    }
}
