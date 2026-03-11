<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Fingerprint;

use Tempest\Http\Request;

use function Tempest\Support\Json\encode;

final class RequestFingerprintGenerator implements HttpFingerprintGenerator
{
    public function generate(Request $request): string
    {
        $payload = [
            'method' => $request->method->value,
            'path' => $request->path,
            'query' => $this->normalize($request->query),
            'body' => $request->raw ?? $this->normalize($request->body),
        ];

        return hash('sha256', encode($payload));
    }

    private function normalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map($this->normalize(...), $value);
        }

        ksort($value);

        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }

        return $value;
    }
}
