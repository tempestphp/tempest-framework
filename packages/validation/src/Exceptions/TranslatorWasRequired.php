<?php

declare(strict_types=1);

namespace Tempest\Validation\Exceptions;

use Exception;

final class TranslatorWasRequired extends Exception
{
    public function __construct()
    {
        parent::__construct('A translator instance is required to generate validation error messages, but none was provided.');
    }
}
