<?php

declare(strict_types=1);

namespace Tempest\Vite\Tests\Integration;

trait HasFixtures
{
    /**
     * @return ($array is true ? array : string)
     */
    private function fixture(string $name, bool $array = true): array|string
    {
        $content = file_get_contents(__DIR__ . "/Fixtures/{$name}");

        return $array
            ? json_decode($content, associative: true)
            : $content;
    }
}
