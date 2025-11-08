<?php

namespace Tempest\View\Exceptions;

use Exception;

final class ViewComponentPathWasNotFound extends Exception
{
    public function __construct(string $fileName)
    {
        parent::__construct("There's no view component file at `{$fileName}`.");
    }
}
