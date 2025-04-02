<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Stringable;
use Tempest\View\Element;
use Tempest\View\Renderers\TempestViewCompiler;
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
        $name = ltrim($this->name, ':');
        $isExpression = str_starts_with($this->name, ':');

        $value = $this->value ?? '';

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

        // Support for falsy-attribute values. When an expression attribute has a falsy value, it won't be rendered at all
        if ($isExpression && $this->wrappingElement instanceof GenericElement) {
            $this->wrappingElement
                ->addRawAttribute(
                    sprintf(
                        '<?php if($%s) { ?>%s="%s"<?php } ?>',
                        $name,
                        str($name)->kebab(),
                        $this->wrappingElement->getAttribute($name),
                    ),
                )
                ->unsetAttribute($name);
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
