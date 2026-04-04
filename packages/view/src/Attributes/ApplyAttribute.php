<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\Support\Arr\ImmutableArray;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\ViewComponentElement;

use function Tempest\Support\str;

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
     * Stringifies an ImmutableArray or plain array of attributes into an HTML attribute string.
     *
     * Rules:
     * - boolean true  → bare attribute name (e.g. `disabled`)
     * - boolean false → omitted
     * - null          → omitted
     * - empty string  → omitted
     * - int / float   → name="value" (cast to string — HTML attributes are always strings)
     * - string        → name="value"
     * - array         → name="space-joined values" (via ExpressionAttribute::resolveValue)
     *
     * Returns 'key="val" key2="val2"' with NO leading space. GenericElement's compile()
     * inserts one space before the raw attribute block at compile time, so adding a leading
     * space here would produce a double space in the rendered output.
     *
     * Note: when this returns '' (all attributes omitted), GenericElement's compile-time
     * space still appears, producing e.g. `<button >`. This is pre-existing framework
     * behaviour shared with ExpressionAttribute and cannot be fixed in this class alone.
     */
    public static function renderAll(ImmutableArray|array $attributes): string
    {
        if (is_array($attributes)) {
            $attributes = new ImmutableArray($attributes);
        }

        $parts = [];

        foreach ($attributes as $name => $value) {
            $attrName = str($name)->kebab()->toString();

            if ($value === true) {
                $parts[] = $attrName;
                continue;
            }

            if ($value === false) {
                continue;
            }

            if ($value === null) {
                continue;
            }

            if ($value === '') {
                continue;
            }

            $resolved = ExpressionAttribute::resolveValue($value);

            if ($resolved !== '') {
                $parts[] = sprintf('%s="%s"', $attrName, $resolved);
            }
        }

        return implode(' ', $parts);
    }
}
