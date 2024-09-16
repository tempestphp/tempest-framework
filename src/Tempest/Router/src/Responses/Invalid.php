<?php

declare(strict_types=1);

namespace Tempest\Router\Responses;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\Status;
use Tempest\Router\IsResponse;
use Tempest\Router\Request;
use Tempest\Router\Response;
use Tempest\Router\Session\Session;

final class Invalid implements Response
{
    use IsResponse;

    public function __construct(
        PsrRequest|Request $request,
        /** @var \Tempest\Validation\Rule[][] $failingRules */
        array $failingRules = [],
    ) {
        $uri = $request instanceof PsrRequest ? (string)$request->getUri() : $request->getUri();
        $body = $request instanceof PsrRequest ? $request->getParsedBody() : $request->getBody();

        $this->addHeader('Location', $uri);
        $this->status = Status::FOUND;
        $this->flash(Session::VALIDATION_ERRORS, $failingRules);
        $this->flash(Session::ORIGINAL_VALUES, $body);
    }
}
