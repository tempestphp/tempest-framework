<?php

namespace Tempest\Intl\MessageFormat;

use Tempest\Intl\MessageFormat\Formatter\FormattedValue;

interface FormattingFunction
{
    /**
     * Identifier of the formatting function.
     */
    public string $name {
        get;
    }

    /**
     * Formats the given value with the given parameters.
     */
    public function format(mixed $value, array $parameters): FormattedValue;
}
