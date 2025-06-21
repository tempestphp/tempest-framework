<?php

namespace Tempest\Internationalization\MessageFormat\Formatter;

interface MessageFormatFunction
{
    /**
     * Name of the function that may be used in a message.
     */
    public string $name {
        get;
    }

    /**
     * Evaluates the function with the given value and parameters.
     */
    public function evaluate(mixed $value, array $parameters): FormattedValue;
}
