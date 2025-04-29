<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\WrapsElement;

use function Tempest\Support\str;

final class PhpDataElement implements Element, WrapsElement
{
    use IsElement;

    public function __construct(
        private readonly string $name,
        private readonly null|string|array $value,
        private readonly Element $wrappingElement,
    ) {}

    public function getWrappingElement(): Element
    {
        return $this->wrappingElement;
    }

    public function compile(): string
    {
        $localVariableName = str($this->name)->ltrim(':')->camel()->toString();
        $isExpression = str_starts_with($this->name, ':');
        $value = $this->value ?? '';

        // We'll declare the variable in PHP right before the actual element
        $variableDeclaration = sprintf(
            '$_%sIsLocal = isset($%s) === false; $%s ??= %s ?? null;',
            $localVariableName,
            $localVariableName,
            $localVariableName,
            $isExpression
                ? ($value ?: 'null')
                : var_export($value, true), // @mago-expect best-practices/no-debug-symbols
        );

        // And we'll remove it right after the element, this way we've created a "local scope"
        // where the variable is only available to that specific element.
        $variableRemoval = sprintf(
            'if ($_%sIsLocal) { unset($%s); }',
            $localVariableName,
            $localVariableName,
        );

        // Support for boolean attributes. When an expression attribute has a falsy value, it won't be rendered at all.
        // When it's "true", it will only render the attribute name and not the "true" value
        $coreElement = $this->unwrap(GenericElement::class);

        if ($isExpression && $coreElement) {
            $attributeName = ltrim($this->name, ':');

            $coreElement
                ->addRawAttribute(new RawConditionalAttribute(
                    name: $attributeName,
                    value: $coreElement->getAttribute($attributeName),
                )->compile())
                ->unsetAttribute($attributeName);
        }

        return sprintf(
            '<?php %s ?>
%s
<?php %s ?>
',
            $variableDeclaration,
            $this->wrappingElement->compile(),
            $variableRemoval,
        );
    }
}
