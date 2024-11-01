<?php

namespace Tempest\View\Exceptions;

use Exception;
use Stringable;

final class InvalidExpressionAttribute extends Exception
{
    public function __construct(Stringable $value)
    {
        $message = sprintf('An expression attribute\'s value cannot contain a nested PHP or echo expression (<?php, <?=, {{, or {!!): %s', $value);

        parent::__construct($message);
    }
}