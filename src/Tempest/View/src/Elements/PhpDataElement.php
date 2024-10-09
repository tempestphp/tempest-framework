<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

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

    public function compile(): string
    {
        $name = ltrim($this->name, ':');
        $isExpression = str_starts_with($this->name, ':');
        $value = str($this->value ?? '');

        if ($value->startsWith([TempestViewCompiler::TOKEN_PHP_OPEN, TempestViewCompiler::TOKEN_PHP_SHORT_ECHO])) {
            $value = $value->replace(TempestViewCompiler::TOKEN_MAPPING, '');
            $isExpression = true;
        }

        $value = $value->toString();

        $variableDeclaration = sprintf(
            '$%s = %s;',
            $name,
            $isExpression
                ? $value ?: 'null'
                : var_export($value, true),
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
