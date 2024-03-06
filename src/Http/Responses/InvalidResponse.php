<?php

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
        private PsrRequest $request,
        private ValidationException $exception,
    ) {
        $this->status = Status::BAD_REQUEST;
        $this->redirect((string) $this->request->getUri());
        $this->getSession()->set('validation_errors', $this->exception->failingRules);
    }
}