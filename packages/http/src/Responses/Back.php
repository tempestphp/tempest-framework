<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Session\PreviousUrl;
use Tempest\Http\Status;

use function Tempest\get;

/**
 * This response is not fit for stateless requests.
 */
final class Back implements Response
{
    use IsResponse;

    public function __construct(?string $fallback = null)
    {
        $this->status = Status::FOUND;

        $previousUrl = get(PreviousUrl::class);
        $request = get(Request::class);

        $url = $previousUrl->get(
            default: $request->headers['referer'] ?? $fallback ?? '/',
        );

        $this->addHeader('Location', value: $url);
    }
}
