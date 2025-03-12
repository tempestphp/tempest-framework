<?php

declare(strict_types=1);

namespace Tempest\Router;

use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Router\Input\PostInputStream;

final readonly class RequestFactory
{
    public function __construct(
        private PostInputStream $inputStream,
    ) {
    }

    public function make(): PsrRequest
    {
        return ServerRequestFactory::fromGlobals(
            body: $this->inputStream->parse(),
        );
    }
}
