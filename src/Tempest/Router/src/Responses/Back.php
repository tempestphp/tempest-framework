<?php

declare(strict_types=1);

namespace Tempest\Router\Responses;

use Tempest\Http\Status;
use Tempest\Router\IsResponse;
use Tempest\Router\Request;
use Tempest\Router\Response;
use Tempest\Router\Session\Session;

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
