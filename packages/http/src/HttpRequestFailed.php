<?php

namespace Tempest\Http;

use Exception;
use Tempest\Core\HasContext;
use Throwable;

/**
 * Represents an HTTP exception.
 */
final class HttpRequestFailed extends Exception implements HasContext
{
    public function __construct(
        public readonly Request $request,
        public readonly Status $status,
        ?string $message = null,
        public readonly ?Response $cause = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message ?: '', $status->value, $previous);
    }

    public function context(): array
    {
        return [
            'request_uri' => $this->request->uri,
            'request_method' => $this->request->method->value,
            'status' => $this->status->value,
            'message' => $this->message,
            'cause' => $this->cause,
            'previous' => $this->getPrevious()?->getMessage(),
        ];
    }
}
