<?php

declare(strict_types=1);

namespace Tempest\Router\Mappers;

use Tempest\Mapper\Mapper;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
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
        $data = $from->body;

        $validator = new Validator();

        $failingRules = [];

        foreach (reflect($to)->getPublicProperties() as $property) {
            $propertyName = $property->getName();

            $data[$propertyName] ??= null;

            $value = $data[$propertyName];

            try {
                // TODO: validateProperty should also validate child properties
                $validator->validateProperty($property, $value);
            } catch (PropertyValidationException $validationException) {
                $failingRules[$propertyName] = $validationException->failingRules;
            }
        }

        if ($failingRules !== []) {
            throw new ValidationException($from, $failingRules);
        }

        return map($data)->with(ArrayToObjectMapper::class)->to($to);
    }
}
