<?php

namespace Tempest\Intl\MessageFormat\Formatter;

use Tempest\Intl\MessageFormat\SelectorFunction;

final class LocalVariable
{
    public function __construct(
        public readonly string $identifier,
        public readonly mixed $value,
        public readonly ?SelectorFunction $function = null,
        public readonly array $parameters = [],
    ) {}
}
