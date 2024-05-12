<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Validation\Exceptions\ValidationException;

final class InvalidResponse implements Response
{
    use IsResponse;

    public function __construct(
        PsrRequest $request,
        ValidationException $exception,
    ) {
        $this->status = Status::BAD_REQUEST;
        $this->redirect((string) $request->getUri());
        $this->flash(Session::VALIDATION_ERRORS, $exception->failingRules);
        $this->flash(Session::ORIGINAL_VALUES, $request->getParsedBody());
    }
}
