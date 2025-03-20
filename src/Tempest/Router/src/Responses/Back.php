<?php

declare(strict_types=1);

namespace Tempest\Router\Responses;

use Tempest\Http\Status;
use Tempest\Router\IsResponse;
use Tempest\Router\Request;
use Tempest\Router\Response;

use function Tempest\get;

final class Back implements Response
{
    use IsResponse;

    public function __construct(?string $fallback = null)
    {
        $this->status = Status::FOUND;
        $request = get(Request::class);

        $url = $request->headers['referer'] ?? $fallback;

        if($url) {
            return $this->addHeader('Location', $url);
        }

        $this->addHeader('Location', '/');
    }
}
