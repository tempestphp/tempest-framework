<?php

declare(strict_types=1);

namespace Tempest\Generation\Php\Exceptions;

use Exception;
use Throwable;

final class PhpCodeWasInvalid extends Exception implements FileGenerationException
{
    public function __construct(Throwable $previous)
    {
        parent::__construct(sprintf('The PHP code could not be parsed: %s', $previous->getMessage()), previous: $previous);
    }
}
