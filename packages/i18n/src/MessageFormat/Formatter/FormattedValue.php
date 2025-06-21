<?php

namespace Tempest\Internationalization\MessageFormat\Formatter;

final class FormattedValue
{
    public function __construct(
        public readonly mixed $value,
        public readonly string $formatted,
    ) {}
}
