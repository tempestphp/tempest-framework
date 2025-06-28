<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\Declaration;

use Tempest\Intl\MessageFormat\Parser\Node\Expression\VariableExpression;

final readonly class InputDeclaration implements Declaration
{
    public function __construct(
        public VariableExpression $expression,
    ) {}
}
