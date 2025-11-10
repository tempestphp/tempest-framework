<?php

namespace Tempest\View\Exceptions;

use Exception;

final class ViewComponentPathWasInvalid extends Exception
{
    public function __construct(string $fileName)
    {
        parent::__construct("View component file names must start with `x-` and end with `.view.php`, instead got `{$fileName}`.");
    }
}
