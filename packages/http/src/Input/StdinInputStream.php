<?php

declare(strict_types=1);

namespace Tempest\Http\Input;

use Tempest\Support\Json;

use function Tempest\Support\str;

final class StdinInputStream implements InputStream
{
    public function parse(): array
    {
        $input = file_get_contents('php://input');

        if (Json\is_valid($input)) {
            return Json\decode($input);
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
