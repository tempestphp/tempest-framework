<?php

declare(strict_types=1);

namespace Tempest\ORM\Exceptions;

use Exception;

final class MissingValuesException extends Exception
{
    public function __construct(string $className, array $missingValues)
    {
        $missingValues = implode(', ', $missingValues);

        $message = "The following required properties are missing in {$className}: {$missingValues}";

        parent::__construct($message);
    }
}
