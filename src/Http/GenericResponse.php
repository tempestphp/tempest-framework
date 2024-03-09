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
        $this->view = $view;

        foreach ($headers as $key => $values) {
            if (! is_array($values)) {
                $values = [$values];
            }

            foreach ($values as $value) {
                $this->header($key, $value);
            }
        }
    }
}
