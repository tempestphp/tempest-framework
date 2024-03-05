<?php

declare(strict_types=1);

namespace Tempest\ORM\Exceptions;

use Exception;

final class CannotMapDataException extends Exception
{
    public function __construct(object|string $objectOrClass, mixed $data)
    {
        $from = is_object($data) ? $data::class : gettype($data);

        $to = is_object($objectOrClass) ? $objectOrClass::class : $objectOrClass;

        parent::__construct("Cannot map {$from} to {$to}");
    }
}
