<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Declaration;

use Tempest\Internationalization\MessageFormat\Parser\Node\Expression\Expression;
use Tempest\Internationalization\MessageFormat\Parser\Node\Variable;

final readonly class LocalDeclaration implements Declaration
{
    public function __construct(
        public Variable $variable,
        public Expression $expression,
    ) {}
}
