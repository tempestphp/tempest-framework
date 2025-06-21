<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Declaration;

use Tempest\Internationalization\MessageFormat\Parser\Node\Expression\VariableExpression;

final readonly class InputDeclaration implements Declaration
{
    public function __construct(
        public VariableExpression $expression,
    ) {}
}
