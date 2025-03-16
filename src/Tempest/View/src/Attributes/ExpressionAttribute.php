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

        if ($this->name === ':class' || $this->name === ':style') {
            $value = self::resolveValue([
                $element->getAttribute(ltrim($this->name, ':')),
                sprintf('<?= %s ?>', $element->getAttribute($this->name)),
            ]);

            $element->setAttribute(
                ltrim($this->name, ':'),
                sprintf('%s', $value),
            );
        } else {
            $element->setAttribute(
                ltrim($this->name, ':'),
                sprintf('<?= ' . \Tempest\View\Attributes\ExpressionAttribute::class . '::resolveValue(%s) ?>', $value),
            );

            $element = new PhpDataElement(
                name: $this->name,
                value: $value->toString(),
                wrappingElement: $element,
            );
        }

        $element->unsetAttribute($this->name);

        return $element;
    }

    public static function resolveValue(mixed $value): string
    {
        if ($value instanceof Stringable) {
            $value = (string) $value;
        }

        if ($value instanceof ArrayInterface) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            $value = trim(implode(' ', $value));
        }

        return (string) $value;
    }
}
