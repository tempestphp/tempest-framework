<?php

namespace Tempest\Router\Input;

use function Tempest\Support\str;

final class StdinPostInputStream implements PostInputStream
{
    public function parse(): array
    {
        $inputStreamData = str(file_get_contents('php://input'))
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
