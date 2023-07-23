<?php

declare(strict_types=1);

namespace Tempest\Support;

final readonly class ArrayHelper
{
    public function unwrap(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $result = array_merge_recursive($result, $this->toArray($key, $value));
        }

        return $result;
    }

    public function toArray(string $key, mixed $value): array
    {
        $keys = explode('.', $key);

        for ($i = array_key_last($keys); $i >= 0; $i--) {
            $currentKey = $keys[$i];

            $value = [$currentKey => $value];
        }

        return $value;
    }
}
