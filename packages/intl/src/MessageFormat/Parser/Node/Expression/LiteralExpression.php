<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\Expression;

use Tempest\Intl\MessageFormat\Parser\Node\Literal\Literal;

final class LiteralExpression extends Expression
{
    public function __construct(
        public readonly Literal $literal,
        ?FunctionCall $function,
        array $attributes,
    ) {
        parent::__construct($function, $attributes);
    }
}
