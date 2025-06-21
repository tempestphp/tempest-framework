<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Expression;

final class FunctionExpression extends Expression
{
    public function __construct(
        FunctionCall $function,
        array $attributes,
    ) {
        parent::__construct($function, $attributes);
    }
}
