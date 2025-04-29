<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;

use function Tempest\get;

final class Back implements Response
{
    use IsResponse;

    public function __construct(?string $fallback = null)
    {
        $this->status = Status::FOUND;
        $request = get(Request::class);

        $url = $request->headers['referer'] ?? $request->getSessionValue(Session::PREVIOUS_URL);

        if ($url) {
            $this->addHeader('Location', $url);
            return;
        }

        if ($fallback) {
            $this->addHeader('Location', $fallback);
            return;
        }

        $this->addHeader('Location', '/');
    }
}
