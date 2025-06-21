<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Expression;

use Tempest\Internationalization\MessageFormat\Parser\Node\Variable;

final class VariableExpression extends Expression
{
    public function __construct(
        public readonly Variable $variable,
        ?FunctionCall $function,
        array $attributes,
    ) {
        parent::__construct($function, $attributes);
    }
}
