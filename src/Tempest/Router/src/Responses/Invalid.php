<?php

declare(strict_types=1);

namespace Tempest\Router\Responses;

use Exception;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\Status;
use Tempest\Router\IsResponse;
use Tempest\Router\Request;
use Tempest\Router\Response;
use Tempest\Router\Session\Session;

final class Invalid implements Response
{
    use IsResponse;

    public function __construct(
        PsrRequest|Request $request,
        /** @var \Tempest\Validation\Rule[][] $failingRules */
        array $failingRules = [],
    ) {
        $referer = $this->getReferer($request);
        $body = ($request instanceof PsrRequest) ? $request->getParsedBody() : $request->body;

        $this->addHeader('Location', $referer);
        $this->status = Status::FOUND;
        $this->flash(Session::VALIDATION_ERRORS, $failingRules);
        $this->flash(Session::ORIGINAL_VALUES, $body);
    }

    private function getReferer(PsrRequest|Request $request): string
    {
        $referer = match (true) {
            $request instanceof Request => $request->headers['referer'] ?? null,
            $request instanceof PsrRequest => $request->getHeader('referer')[0] ?? null,
        };

        if (! $referer) {
            throw new Exception("No referer found, could not redirect (this shouldn't happen, please create a bug report)");
        }

        return $referer;
    }
}
