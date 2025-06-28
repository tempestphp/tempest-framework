<?php

namespace Tempest\View\Exceptions;

use Exception;

final class ClosingTagWasInvalid extends Exception
{
    public function __construct(string $openTag, string $closingTag)
    {
        parent::__construct("Invalid closing tag `{$closingTag}` for opening tag `{$openTag}`");
    }
}
