<?php

declare(strict_types=1);

namespace Tempest\View\Exceptions;

use Exception;

final class CompileError extends Exception
{
    public function __construct(string $content)
    {
        parent::__construct("Could not compile {$content}");
    }
}
