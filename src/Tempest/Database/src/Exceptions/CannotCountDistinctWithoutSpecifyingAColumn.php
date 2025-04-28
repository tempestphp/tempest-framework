<?php

namespace Tempest\Database\Exceptions;

use LogicException;

final class CannotCountDistinctWithoutSpecifyingAColumn extends LogicException
{
    public function __construct()
    {
        parent::__construct('Cannot count distinct without specifying a column');
    }
}
