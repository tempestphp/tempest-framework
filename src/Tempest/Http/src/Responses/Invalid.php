<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Exception;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;

final class Invalid implements Response
{
    use IsResponse;

    public function __construct(
        PsrRequest|Request $request,
        /** @var \Tempest\Validation\Rule[][] $failingRules */
        array $failingRules = [],
    ) {
        $referer = $this->getReferer($request);
        $body = $request instanceof PsrRequest ? $request->getParsedBody() : $request->getBody();

        $this->addHeader('Location', $referer);
        $this->status = Status::FOUND;
        $this->flash(Session::VALIDATION_ERRORS, $failingRules);
        $this->flash(Session::ORIGINAL_VALUES, $body);
    }

    private function getReferer(PsrRequest|Request $request): string
    {
        $referer = match (true) {
            $request instanceof Request => $request->getHeaders()['referer'] ?? null,
            $request instanceof PsrRequest => $request->getHeader('referer')[0] ?? null,
        };

        if (! $referer) {
            throw new Exception("No referer found, could not redirect (this shouldn't happen, please create a bug report)");
        }

        return $referer;
    }
}
