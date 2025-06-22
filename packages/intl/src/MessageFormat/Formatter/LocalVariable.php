<?php

namespace Tempest\Intl\MessageFormat\Formatter;

use Tempest\Intl\MessageFormat\SelectorFunction;

final readonly class LocalVariable
{
    public function __construct(
        public string $identifier,
        public mixed $value,
        public ?SelectorFunction $function = null,
        public array $parameters = [],
    ) {}
}
