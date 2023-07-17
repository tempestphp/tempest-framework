<?php

declare(strict_types=1);

namespace Tempest\ORM\Exceptions;

use Exception;

final class MissingValuesException extends Exception
{
    public function __construct(object|string $objectOrClass, array $missingValues)
    {
        if (is_string($objectOrClass)) {
            $className = $objectOrClass;
        } else {
            $className = $objectOrClass::class;
        }

        $missingValues = implode(', ', $missingValues);

        $message = "The following required properties are missing in {$className}: {$missingValues}";

        parent::__construct($message);
    }
}
