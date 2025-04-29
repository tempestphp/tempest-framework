<?php

declare(strict_types=1);

namespace Tempest\Support\Json;

use JsonException;

use function json_decode;
use function json_encode;

use const JSON_BIGINT_AS_STRING;
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Decodes a json encoded string into a dynamic variable.
 *
 * @throws Exception\DecodeException If an error occurred.
 */
function decode(string $json, bool $associative = true): mixed
{
    try {
        /** @var mixed $value */
        $value = json_decode($json, $associative, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
    } catch (JsonException $jsonException) {
        throw new Exception\DecodeException(sprintf('%s.', $jsonException->getMessage()), $jsonException->getCode(), $jsonException);
    }

    return $value;
}

/**
 * Returns a string containing the JSON representation of the supplied value.
 *
 * @throws Exception\EncodeException If an error occurred.
 *
 * @return non-empty-string
 */
function encode(mixed $value, bool $pretty = false, int $flags = 0): string
{
    $flags |= JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR;

    if ($pretty) {
        $flags |= JSON_PRETTY_PRINT;
    }

    try {
        /** @var non-empty-string $json */
        $json = json_encode($value, $flags);
    } catch (JsonException $jsonException) {
        throw new Exception\EncodeException(sprintf('%s.', $jsonException->getMessage()), $jsonException->getCode(), $jsonException);
    }

    return $json;
}
