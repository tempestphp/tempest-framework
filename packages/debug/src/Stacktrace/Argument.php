<?php

namespace Tempest\Debug\Stacktrace;

use Tempest\Support\Json;

final readonly class Argument
{
    public function __construct(
        public string|int $name,
        public string $compact,
        public ?string $json,
    ) {}

    public static function make(string|int $name, mixed $value): self
    {
        return new self(
            name: $name,
            compact: self::serializeToCompactString($value),
            json: self::serialize($value),
        );
    }

    private static function serializeToCompactString(mixed $value): string
    {
        return match (true) {
            is_null($value) => 'null',
            is_bool($value) => sprintf('bool<%s>', $value ? 'true' : 'false'),
            is_int($value) => (string) $value,
            is_float($value) => (string) $value,
            is_string($value) => mb_strlen($value) > 50
                ? sprintf('string<%s>', mb_strlen($value))
                : sprintf('"%s"', $value),
            is_array($value) => sprintf('array<%s>', count($value)),
            is_object($value) => sprintf('object<%s>', $value::class),
            is_resource($value) => 'resource',
            default => get_debug_type($value),
        };
    }

    private static function serialize(mixed $value): ?string
    {
        $serialized = Json\encode($value, pretty: true);

        if ($serialized === '{}') {
            return null;
        }

        return $serialized;
    }
}
