<?php

namespace Tempest\Router\Input;

use function Tempest\Support\str;

final class StdinPostInputStream implements PostInputStream
{
    public function parse(): array
    {
        return str(file_get_contents('php://input'))
            ->explode('&')
            ->mapWithKeys(function (string $value) {
                $parts = explode('=', $value, 2);

                yield $parts[0] => $parts[1] ?? '';
            })
            ->toArray();
    }
}
