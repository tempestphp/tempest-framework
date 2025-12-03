<?php

declare(strict_types=1);

namespace Tempest\Http\Mappers;

use Tempest\Http\Request;
use Tempest\Http\RequestParametersIncludedReservedNames;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Validator;

use function Tempest\map;
use function Tempest\Support\arr;

final readonly class RequestToObjectMapper implements Mapper
{
    public function __construct(
        private Validator $validator,
    ) {}

    public function canMap(mixed $from, mixed $to): bool
    {
        return $from instanceof Request;
    }

    public function map(mixed $from, mixed $to): array|object
    {
        /** @var Request $from */
        $data = [...$from->files, ...$from->body, ...$from->query];

        if (is_a($to, Request::class, true)) {
            $invalidReservedProperties = arr(new ClassReflector(Request::class)->getProperties())
                ->map(fn (PropertyReflector $property) => $property->getName())
                ->filter(fn (string $property) => array_key_exists($property, $data));

            if ($invalidReservedProperties->isNotEmpty()) {
                throw new RequestParametersIncludedReservedNames($to, $invalidReservedProperties);
            }

            $data = [
                ...[
                    'method' => $from->method,
                    'uri' => $from->uri,
                    'body' => $from->body,
                    'headers' => $from->headers,
                    'path' => $from->path,
                    'query' => $from->query,
                    'files' => $from->files,
                    'cookies' => $from->cookies,
                ],
                ...$data,
            ];
        }

        $failingRules = $this->validator->validateValuesForClass($to, $data);

        if ($failingRules !== []) {
            $targetClass = is_string($to) ? $to : $to::class;
            throw $this->validator->createValidationFailureException($failingRules, $from, $targetClass);
        }

        return map($data)->with(ArrayToObjectMapper::class)->to($to);
    }
}
