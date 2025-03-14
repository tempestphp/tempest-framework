<?php

namespace Tempest\View\Exceptions;

use Exception;
use Tempest\View\Renderers\TempestViewCompiler;

final class InvalidDataAttribute extends Exception
{
    public function __construct(string $name, string $value)
    {
        $value = str_replace(TempestViewCompiler::TOKEN_MAPPING, array_keys(TempestViewCompiler::TOKEN_MAPPING), $value);

        $message = sprintf("An data attribute's value cannot contain a PHP expression (<?php or <?=), use expression attributes instead: 
× %s=\"%s\"
✓ %s=\"%s\"",
            $name,
            $value,
            ":$name",
            trim(str_replace(array_keys(TempestViewCompiler::TOKEN_MAPPING), '', $value)),
        );

        parent::__construct($message);
    }
}