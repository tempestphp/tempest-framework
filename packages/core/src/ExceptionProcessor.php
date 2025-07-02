<?php

namespace Tempest\Core;

use Throwable;

interface ExceptionProcessor
{
    /**
     * Processes the given exception.
     */
    public function process(Throwable $throwable): void;
}
