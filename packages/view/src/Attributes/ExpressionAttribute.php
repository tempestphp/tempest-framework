<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Stringable;
use Tempest\Support\Arr\ArrayInterface;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\GenericElement;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\Exceptions\ExpressionAttributeWasInvalid;
use Tempest\View\Parser\TempestViewCompiler;

use function Tempest\Support\str;

final readonly class ExpressionAttribute implements Attribute
{
    public function __construct(
        private string $name,
    ) {}

    public function apply(Element $element): Element
    {
        $value = str($element->getAttribute($this->name));

        if ($value->startsWith(['{{', '{!!', ...TempestViewCompiler::PHP_TOKENS])) {
            throw new ExpressionAttributeWasInvalid($value);
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
            $attributeName = ltrim($this->name, ':');

            $element
                ->addRawAttribute($this->compileAttribute($attributeName, $value->toString()))
                ->unsetAttribute($attributeName);
        }

        $element->unsetAttribute($this->name);

        return $element;
    }

    private function compileAttribute(string $name, string $value): string
    {
        return sprintf(
            "<?= %s::render(name: '%s', value: %s) ?>",
            self::class,
            $name,
            $value,
        );
    }

    public static function render(string $name, mixed $value): string
    {
        if ($value === true) {
            return str($name)->kebab()->toString();
        }

        if (! $value) {
            return '';
        }

        return sprintf(
            '%s="%s"',
            str($name)->kebab(),
            ExpressionAttribute::resolveValue($value),
        );
    }

    public static function resolveValue(mixed $value): string
    {
        if ($value instanceof Stringable) {
            $value = (string)$value;
        }

        if ($value instanceof ArrayInterface) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            $value = trim(implode(' ', $value));
        }

        return (string)$value;
    }
}
