<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\Renderers\TempestViewCompiler;
use Tempest\View\WrapsElement;

use function Tempest\Support\str;

final class PhpDataElement implements Element, WrapsElement
{
    use IsElement;

    public function __construct(
        private readonly string $name,
        private readonly ?string $value,
        private readonly Element $wrappingElement,
    ) {
    }

    public function getWrappingElement(): Element
    {
        return $this->wrappingElement;
    }

    public function compile(): string
    {
        $name = ltrim($this->name, ':');
        $isExpression = str_starts_with($this->name, ':');

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
                ? ($value ?: 'null')
                : var_export($value, true), // @mago-expect best-practices/no-debug-symbols
        );

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
