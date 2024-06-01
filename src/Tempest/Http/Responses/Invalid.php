<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tempest\Validation\Exceptions\ValidationException;

final class Invalid implements Response
{
    use IsResponse;

    public function __construct(
        PsrRequest $request,
        ValidationException $exception,
    ) {
        $this->addHeader('Location', (string) $request->getUri());
        $this->status = Status::FOUND;
        $this->flash(Session::VALIDATION_ERRORS, $exception->failingRules);
        $this->flash(Session::ORIGINAL_VALUES, $request->getParsedBody());
    }
}
