<?php

declare(strict_types=1);

namespace Tempest\HttpApi;

use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class ApiResponse implements Response
{
    use IsResponse;

    public function __construct(
        Status $status,
        array $body,
        array $headers = [],
        array $extraData = [],
        ?string $dataWrappingObject = 'data',
    ) {
        $this->status = $status;

        $this->body = $dataWrappingObject
            ? [
                $dataWrappingObject => $body,
                ...$extraData,
            ]
            : $body;

        $this->view = null;
        $headers = [
            ...$headers,
            'Content-Type' => 'application/json',
        ];

        foreach ($headers as $key => $values) {
            if (! is_array($values)) {
                $values = [$values];
            }

            foreach ($values as $value) {
                $this->addHeader($key, $value);
            }
        }
    }
}
