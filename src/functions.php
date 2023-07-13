<?php

use Tempest\Http\GenericRequest;
use Tempest\Http\GenericResponse;
use Tempest\Http\Method;
use Tempest\Http\Status;
use Tempest\Interfaces\Request;
use Tempest\Interfaces\Response;
use Tempest\Interfaces\View;
use Tempest\View\GenericView;

function path(string ...$parts): string
{
    $path = implode('/', $parts);

    return str_replace(
        ['//', '\\', '\\\\'],
        ['/', '/', '/'],
        $path,
    );
}

function view(string $path): View
{
    return new GenericView($path);
}

function request(string $uri, array $body = []): Request
{
    return new GenericRequest(Method::GET, $uri, $body);
}

function response(string $body = ''): Response
{
    return new GenericResponse(Status::HTTP_200, $body);
}
