<?php

declare(strict_types=1);

namespace {

    use Tempest\Debug\Debug;

    if (! function_exists('lw')) {
        function lw(mixed ...$input): void
        {
            Debug::resolve()->log($input);
        }
    }

    if (! function_exists('ld')) {
        function ld(mixed ...$input): void
        {
            Debug::resolve()->log($input);
            die();
        }
    }

    if (! function_exists('ll')) {
        function ll(mixed ...$input): void
        {
            Debug::resolve()->log($input, writeToOut: false);
        }
    }

    // Alias dd to ld
    if (! function_exists('dd')) {
        function dd(mixed ...$input): void
        {
            ld(...$input);
        }
    }

    // Alias dump to lw
    if (! function_exists('dump')) {
        function dump(mixed ...$input): void
        {
            lw(...$input);
        }
    }
}
