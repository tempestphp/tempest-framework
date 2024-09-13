<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;

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
