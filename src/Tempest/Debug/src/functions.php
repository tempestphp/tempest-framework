<?php

declare(strict_types=1);

namespace {

    use Tempest\Debug\Debug;

    if (! function_exists('lw')) {
        /**
         * Writes the given `$input` to the logs, and dumps it.
         * @see \Tempest\Debug\Debug::log()
         */
        function lw(mixed ...$input): void
        {
            Debug::resolve()->log($input);
        }
    }

    if (! function_exists('ld')) {
        /**
         * Writes the given `$input` to the logs, dumps it, and stops the execution of the script.
         * @see \Tempest\Debug\Debug::log()
         */
        function ld(mixed ...$input): void
        {
            Debug::resolve()->log($input);
            die();
        }
    }

    if (! function_exists('ll')) {
        /**
         * Writes the given `$input` to the logs.
         * @see \Tempest\Debug\Debug::log()
         */
        function ll(mixed ...$input): void
        {
            Debug::resolve()->log($input, writeToOut: false);
        }
    }

    if (! function_exists('dd')) {
        /**
         * Writes the given `$input` to the logs, dumps it, and stops the execution of the script.
         * @see ld()
         * @see \Tempest\Debug\Debug::log()
         */
        function dd(mixed ...$input): void
        {
            ld(...$input);
        }
    }

    if (! function_exists('dump')) {
        /**
         * Writes the given `$input` to the logs, and dumps it.
         * @see lw()
         * @see \Tempest\Debug\Debug::log()
         */
        function dump(mixed ...$input): void
        {
            lw(...$input);
        }
    }
}
