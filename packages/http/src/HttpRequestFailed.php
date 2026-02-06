<?php

namespace Tempest\Http;

use Exception;
use Tempest\Core\ProvidesContext;

/**
 * Stops the request's execution and return a response with the given status. Optionally, a message may be provided.
 */
final class HttpRequestFailed extends Exception implements ProvidesContext
{
    /**
     * @param Status $status The HTTP status code to send as a response.
     * @param string|null $message An optional message that will be displayed to the client.
     * @param Response|null $cause The response that caused the failure, if any.
     * @param Request|null $request The request that failed, for debug purposes.
     */
    public function __construct(
        private(set) readonly Status $status,
        ?string $message = null,
        private(set) readonly ?Response $cause = null,
        private(set) readonly ?Request $request = null,
    ) {
        parent::__construct(
            message: $message ?: '',
            code: $status->value,
        );
    }

    public function context(): array
    {
        return array_filter([
            'request_uri' => $this->request?->uri,
            'request_method' => $this->request?->method->value,
            'status_code' => $this->status->value,
            'original_response' => $this->cause ? $this->cause::class : null,
        ]);
    }
}
