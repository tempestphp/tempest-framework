<?php

namespace Tempest\Testing\BypassMock;

final class Bypass
{
    /** holds php tokens, e.g. final | readonly */
    private static array $tokens = [];

    public static function enable(bool $readonly = true, bool $final = true): void
    {
        self::addToTokens($readonly, $final);

        stream_filter_register(Filter::NAME, Filter::class);

        stream_wrapper_unregister(Wrapper::Protocol);
        stream_wrapper_register(Wrapper::Protocol, Wrapper::class);
    }

    public static function getTokens(): array
    {
        return self::$tokens;
    }

    private static function addToTokens(bool $readonly, bool $final): void
    {
        if ($readonly && PHP_VERSION_ID >= 80100) {
            self::$tokens[T_READONLY] = 'readonly';
        }

        if ($final) {
            self::$tokens[T_FINAL] = 'final';
        }
    }
}
