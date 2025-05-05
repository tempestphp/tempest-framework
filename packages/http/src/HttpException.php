<?php

namespace Tempest\Http;

use Exception;

/**
 * Represents an HTTP exception.
 */
final class HttpException extends Exception
{
    public function __construct(
        public readonly Status $status,
        ?string $message = null,
        public readonly ?Response $response = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message ?: '', $status->value, $previous);
    }
}
