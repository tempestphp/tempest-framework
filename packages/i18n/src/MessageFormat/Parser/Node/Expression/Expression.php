<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\Expression;

use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\Placeholder;

abstract class Expression implements Placeholder
{
    /**
     * @param (Attribute)[] $attributes
     */
    public function __construct(
        public readonly ?FunctionCall $function,
        public readonly array $attributes,
    ) {}
}
