<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\Support\Arr\ImmutableArray;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\ViewComponentElement;

final readonly class ApplyAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        $value = $element->consumeAttribute(':apply');

        if ($value === null || trim($value) === '') {
            return $element;
        }

        if ($element instanceof ViewComponentElement) {
            $element->setApplyExpression($value);

            return $element;
        }

        $element->addRawAttribute(sprintf(
            '<?= \%s::renderAll(%s) ?>',
            self::class,
            $value,
        ));

        return $element;
    }

    /**
     * Renders an ImmutableArray or plain array of attributes as an HTML attribute string.
     *
     * Boolean true emits a bare attribute name (e.g. `disabled`).
     * Boolean false, null, and empty string are omitted entirely.
     * All other values are rendered as name="value" pairs via ExpressionAttribute::render().
     *
     * Returns a string with a leading space when not empty, or an empty string.
     */
    public static function renderAll(ImmutableArray|array $attributes): string
    {
        if (is_array($attributes)) {
            $attributes = new ImmutableArray($attributes);
        }

        $parts = [];

        foreach ($attributes as $name => $value) {
            $rendered = ExpressionAttribute::render((string) $name, $value);

            if ($rendered !== '') {
                $parts[] = $rendered;
            }
        }

        return $parts === [] ? '' : ' ' . implode(' ', $parts);
    }
}