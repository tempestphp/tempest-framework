<?php

declare(strict_types=1);

namespace Tempest\Support;

final class StringHelper
{

    public static function join(array $strings): string
    {
        $last = array_pop($strings);

        if ($strings) {
            return implode(', ', $strings) . ' ' . 'and' . ' ' . $last;
        }

        return $last;
    }

}
