<?php

namespace Tempest\Mailer;

use Exception;

final class InvalidFromAddress extends Exception
{
    public function __construct()
    {
        parent::__construct("There's no valid from address configured.");
    }
}
