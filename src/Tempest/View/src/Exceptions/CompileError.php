<?php

declare(strict_types=1);

namespace Tempest\View\Exceptions;

use Exception;
use Throwable;

final class CompileError extends Exception
{
    public function __construct(string $content, ?Throwable $previous = null)
    {
        parent::__construct("Could not compile {$content}", previous: $previous);
    }
}
