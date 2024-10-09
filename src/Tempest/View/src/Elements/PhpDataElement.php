<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class PhpDataElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $name,
        private readonly mixed $value,
        private readonly Element $wrappingElement,
    ) {
    }

    public function compile(): string
    {
        $name = ltrim($this->name, ':');
        $isExpression = str_starts_with($this->name, ':');

        $variableDeclaration = sprintf(
            '$%s = %s;',
            $name,
            $isExpression
                ? $this->value ?? 'null'
                : var_export($this->value, true),
        );

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
