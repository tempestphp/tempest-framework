<?php

declare(strict_types = 1);

namespace Tempest\Http\Session;

use Tempest\Http\Response\Response;
use Tempest\Http\Response\ResponseProcessor;

final readonly class SessionResponseProcessor implements ResponseProcessor {
    public function __construct(
        private Session $session,
    ) {}

    public function process(Response $response): Response {
        $this->session->persist();

        return $response;
    }
}
