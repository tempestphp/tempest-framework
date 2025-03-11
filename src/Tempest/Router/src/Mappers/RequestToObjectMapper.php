<?php

declare(strict_types=1);

namespace Tempest\Router\Mappers;

use Tempest\Mapper\Mapper;
use Tempest\Router\Request;
use Tempest\Validation\Exceptions\PropertyValidationException;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Validator;

use function Tempest\map;
use function Tempest\reflect;

final readonly class RequestToObjectMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        return $from instanceof Request;
    }

    public function map(mixed $from, mixed $to): array|object
    {
        /** @var Request $from */
        $object = map($from->body)->to($to);

        // We perform a new round of validation on the newly constructed object
        // because we want to be sure that required uninitialized properties are also validated.
        // This doesn't happen in the ArrayToObject mapper because we are more lenient there by design
        // TODO: The better approach would be to have this RequestToObjectMapper be totally independent of ArrayToObjectMapper
        $validator = new Validator();

        $failingRules = [];

        foreach (reflect($object)->getPublicProperties() as $property) {
            $value = $property->isInitialized($object) ? $property->getValue($object) : null;

            try {
                $validator->validateProperty($property, $value);
            } catch (PropertyValidationException $validationException) {
                $failingRules[$property->getName()] = $validationException->failingRules;
            }
        }

        if ($failingRules !== []) {
            throw new ValidationException($object, $failingRules);
        }

        return $object;
    }
}
