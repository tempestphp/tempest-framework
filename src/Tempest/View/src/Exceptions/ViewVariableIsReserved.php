<?php

namespace Tempest\View\Exceptions;

use Exception;

final class ViewVariableIsReserved extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct('Cannot use reserved variable name `' . $name . '`');
    }
}
