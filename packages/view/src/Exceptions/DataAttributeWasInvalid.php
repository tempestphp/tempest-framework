<?php

declare(strict_types=1);

namespace Tempest\View\Exceptions;

use Exception;

final class DataAttributeWasInvalid extends Exception
{
    public function __construct(string $name, string $value)
    {
        $message = sprintf("A data attribute's value cannot contain a PHP expression (<?php or <?=), use expression attributes instead: 
× %s=\"%s\"
✓ %s=\"%s\"", $name, $value, ":{$name}", $value);

        parent::__construct($message);
    }
}
