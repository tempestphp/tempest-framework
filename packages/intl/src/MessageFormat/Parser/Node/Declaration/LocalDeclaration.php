<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\Declaration;

use Tempest\Intl\MessageFormat\Parser\Node\Expression\Expression;
use Tempest\Intl\MessageFormat\Parser\Node\Variable;

final readonly class LocalDeclaration implements Declaration
{
    public function __construct(
        public Variable $variable,
        public Expression $expression,
    ) {}
}
