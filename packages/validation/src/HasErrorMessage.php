<?php

namespace Tempest\Validation;

interface HasErrorMessage
{
    /**
     * Returns a plain-text validation error message.
     */
    public function getErrorMessage(): string;
}
