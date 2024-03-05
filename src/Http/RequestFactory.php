<?php

declare(strict_types=1);

namespace Tempest\Http;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

final readonly class RequestFactory
{
    public function make(): PsrRequest
    {
        return ServerRequestFactory::fromGlobals();
    }
}
