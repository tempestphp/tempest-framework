<?php

namespace Tempest\Http;

use Exception;
use Tempest\Core\HasContext;

/**
 * Represents an HTTP exception.
 */
final class HttpException extends Exception implements HasContext
{
    public function __construct(
        public readonly Status $status,
        ?string $message = null,
        public readonly ?Response $cause = null,
        public ?Response $response = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message ?: '', $status->value, $previous);
    }

    public function context(): array
    {
        return [
            'status' => $this->status->value,
            'message' => $this->message,
            'cause' => $this->cause,
            'previous' => $this->getPrevious()?->getMessage(),
        ];
    }
}
