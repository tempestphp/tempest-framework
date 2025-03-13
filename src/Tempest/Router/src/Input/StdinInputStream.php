<?php

namespace Tempest\Router\Input;

use function Tempest\Support\str;

final class StdinInputStream implements InputStream
{
    public function parse(): array
    {
        $input = file_get_contents('php://input');

        if (json_validate($input)) {
            return json_decode($input, true);
        }

        $inputStreamData = str($input)
            ->explode('&')
            ->mapWithKeys(function (string $item) {
                $parts = explode('=', $item, 2);

                $key = $parts[0];

                $value = $_POST[str_replace('.', '_', $key)] ?? $parts[1] ?? '';

                yield $key => $value;
            })
            ->toArray();

        return $_POST + $inputStreamData;
    }
}
