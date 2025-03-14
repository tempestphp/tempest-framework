<?php

declare(strict_types=1);

namespace Tempest\View\Exceptions;

use Exception;
use Stringable;
use Tempest\View\Renderers\TempestViewCompiler;

final class InvalidExpressionAttribute extends Exception
{
    public function __construct(Stringable $value)
    {
        $value = str_replace(TempestViewCompiler::TOKEN_MAPPING,array_keys(TempestViewCompiler::TOKEN_MAPPING), $value);

        $message = sprintf("An expression attribute's value cannot contain a nested PHP or echo expression (<?php, <?=, {{, or {!!): %s", $value);

        parent::__construct($message);
    }
}
