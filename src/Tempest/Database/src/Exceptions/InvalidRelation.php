<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;
use Throwable;

final class InvalidRelation extends Exception
{
    public static function inversePropertyNotFound(string $modelClass, string $modelProperty, string $relatedClass): self
    {
        return new self(
            "Unable to determine inverse property for {$modelClass}::{$modelProperty}, ".
            "Related class {$relatedClass} doesn't have a property of type {$modelClass}."
        );
    }

    public static function inversePropertyMissing(
        string $modelClass,
        string $modelProperty,
        string $relatedClass,
        string $propertyName
    ): self {
        return new self(
            "Unable to determine inverse property for {$modelClass}::{$modelProperty}, ".
            "Related class {$relatedClass} doesn't have a property named {$propertyName}."
        );
    }

    public static function inversePropertyInvalidType(
        string $modelClass,
        string $modelProperty,
        string $relatedClass,
        string $propertyName,
        string $expectedType,
        string $actualType,
    ): self {
        return new self(
            "Unable to determine inverse property for {$modelClass}::{$modelProperty}, ".
            "Related class {$relatedClass} expected to have property {$propertyName} of type {$expectedType}, ".
            "got {$actualType}."
        );
    }

    private function __construct(string $message)
    {
        parent::__construct($message);
    }
}
