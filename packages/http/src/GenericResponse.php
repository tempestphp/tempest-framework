<?php

declare(strict_types=1);

namespace Tempest\Http;

use Generator;
use JsonSerializable;
use Tempest\View\View;

final class GenericResponse implements Response
{
    use IsResponse;

    public function __construct(
        Status $status,
        Generator|View|string|array|JsonSerializable|null $body = null,
        array $headers = [],
        ?View $view = null,
    ) {
        $this->status = $status;
        $this->body = $body;
        $this->view = $view;

        $this->addHeaders($headers);
    }
}
