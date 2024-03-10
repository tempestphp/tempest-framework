<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\IsResponse;
use Tempest\Http\Response;
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
        // TODO: add these names as constants somewhere
        // TODO: mapping empty field to number results in type/validation error
        $this->flash('validation_errors', $exception->failingRules);
        $this->flash('original_values', $request->getParsedBody());
    }
}
