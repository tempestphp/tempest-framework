<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\View\View;

final class GenericResponse implements Response
{
    use IsResponse;

    public function __construct(
        Status $status,
        string|array|null $body = null,
        array $headers = [],
        ?View $view = null,
    ) {
        $this->status = $status;
        $this->body = $body;
        $this->headers = $headers;
        $this->view = $view;
    }
}
