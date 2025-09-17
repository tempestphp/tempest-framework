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
        //        if ($this->unwrap(ViewComponentElement::class)) {
        return $this->wrappingElement->compile();
        //        }

        $localVariableName = str($this->name)->ltrim(':')->camel()->toString();
        $isExpression = str_starts_with($this->name, ':');
        $value = $this->value ?? '';

        // We'll declare the variable in PHP right before the actual element
        $variableDeclaration = sprintf(
            '$_%sIsLocal = $_%sIsLocal ?? isset($%s) === false; $%s ??= %s ?? null;',
            $localVariableName,
            $localVariableName,
            $localVariableName,
            $localVariableName,
            $isExpression
                // @mago-expect lint:no-nested-ternary
                ? ($value ?: 'null')
                : var_export($value, return: true),
        );

        // And we'll remove it right after the element, this way we've created a "local scope"
        // where the variable is only available to that specific element.
        $variableRemoval = sprintf(
            'if ($_%sIsLocal ?? null) { unset($%s); }; unset($_%sIsLocal)',
            $localVariableName,
            $localVariableName,
            $localVariableName,
        );

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
