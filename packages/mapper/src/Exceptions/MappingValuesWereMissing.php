<?php

declare(strict_types=1);

namespace Tempest\Mapper\Exceptions;

use Exception;

final class MappingValuesWereMissing extends Exception
{
    public function __construct(object|string $objectOrClass, array $missingValues)
    {
        $className = is_string($objectOrClass) ? $objectOrClass : $objectOrClass::class;

        $missingValues = implode(', ', $missingValues);

        $message = "The following required properties are missing in {$className}: {$missingValues}";

        parent::__construct($message);
    }
}
