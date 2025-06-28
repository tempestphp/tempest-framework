<?php

declare(strict_types=1);

namespace Tempest\Http\Mappers;

use Tempest\Http\Request;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
use Tempest\Validation\Exceptions\ValidationFailed;
use Tempest\Validation\Validator;

use function Tempest\map;

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

        if (is_a($to, Request::class, true)) {
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
                ...$from->files,
                ...$from->query,
                ...$data,
            ];
        }

        $failingRules = new Validator()->validateValuesForClass($to, $data);

        if ($failingRules !== []) {
            throw new ValidationFailed($from, $failingRules);
        }

        return map($data)->with(ArrayToObjectMapper::class)->to($to);
    }
}
