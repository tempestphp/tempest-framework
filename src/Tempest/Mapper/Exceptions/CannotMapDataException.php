<?php

declare(strict_types=1);

namespace Tempest\Mapper\Exceptions;

use Exception;

final class CannotMapDataException extends Exception
{
    public function __construct(mixed $data, object|string $objectOrClass)
    {
        $from = get_debug_type($data);

        $to = is_object($objectOrClass) ? $objectOrClass::class : $objectOrClass;

        parent::__construct("Cannot map {$from} to {$to}");
    }
}
