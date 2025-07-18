<?php

namespace Tempest\Http\Session;

use Exception;

final class CsrfTokenDidNotMatch extends Exception
{
    public function __construct()
    {
        parent::__construct('The CSRF token did not match.');
    }
}
