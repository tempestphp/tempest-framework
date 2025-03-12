<?php

declare(strict_types=1);

namespace Tempest\Router;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

use function Tempest\Support\str;

final readonly class RequestFactory
{
    public function make(): PsrRequest
    {
        $body = str(file_get_contents('php://input'))
            ->explode('&')
            ->mapWithKeys(function (string $value) {
                $parts = explode('=', $value, 2);

                yield $parts[0] => $parts[1] ?? '';
            })
            ->toArray();

        return ServerRequestFactory::fromGlobals(
            body: $body,
        );
    }
}
