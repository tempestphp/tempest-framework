<?php

declare(strict_types=1);

namespace Tempest\Router;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Router\Input\InputStream;

final readonly class RequestFactory
{
    public function __construct(
        private InputStream $inputStream,
    ) {}

    public function make(): PsrRequest
    {
        return ServerRequestFactory::fromGlobals(
            body: $this->inputStream->parse(),
        );
    }
}
