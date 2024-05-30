<?php

declare(strict_types=1);

namespace Tempest\Support;

final class LanguageHelper
{
    /**
     * @param string[] $parts
     *
     * @return string
     */
    public static function join(array $parts): string
    {
        $last = array_pop($parts);

        if ($parts) {
            return implode(', ', $parts) . ' ' . 'and' . ' ' . $last;
        }

        return $last;
    }
}
