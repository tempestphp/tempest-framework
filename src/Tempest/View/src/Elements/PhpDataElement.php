<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use function Tempest\Support\str;
use Tempest\View\Element;
use Tempest\View\Renderers\TempestViewCompiler;

final class PhpDataElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $name,
        private readonly ?string $value,
        private readonly Element $wrappingElement,
    ) {
    }

    public function getAttribute(string $name): string|null
    {
        $name = ltrim($name, ':');

        return $this->wrappingElement->getAttribute($name)
            ?? $this->attributes[":{$name}"]
            ?? $this->attributes[$name]
            ?? null;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->wrappingElement->{$name}(...$arguments);
    }

    public function compile(): string
    {
        $name = ltrim($this->name, ':');
        $isExpression = str_starts_with($this->name, ':');

        // TODO: what if not stringable value? Eg. object passed to view component?
        $value = str($this->value ?? '');

        // If the value of an attribute is PHP code, it's automatically promoted to an expression with the PHP tags stripped
        if ($value->startsWith([TempestViewCompiler::TOKEN_PHP_OPEN, TempestViewCompiler::TOKEN_PHP_SHORT_ECHO])) {
            $value = $value->replace(TempestViewCompiler::TOKEN_MAPPING, '');
            $isExpression = true;
        }

        $value = $value->toString();

        // We'll declare the variable in PHP right before the actual element
        $variableDeclaration = sprintf(
            '$%s ??= %s ?? null;',
            $name,
            $isExpression
                ? $value ?: 'null'
                : var_export($value, true),
        );

        $wrappedViewComponent = $this->wrappingElement->unwrap(ViewComponentElement::class);

        if ($wrappedViewComponent !== null) {
            $this->wrappingElement->setAttribute($name, "<?= \${$name} ?>");
        }

        // And we'll remove it right after the element, this way we've created a "local scope"
        // where the variable is only available to that specific element.
        $variableRemoval = sprintf(
            'unset($%s);',
            $name,
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
