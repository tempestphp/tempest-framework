<?php

namespace Tempest\Intl\MessageFormat\Formatter;

final readonly class FormattedValue
{
    public function __construct(
        public mixed $value,
        public string $formatted,
    ) {}
}
