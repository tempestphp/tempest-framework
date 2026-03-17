<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Fingerprint;

use RuntimeException;
use UnitEnum;

use function Tempest\Support\Json\encode;

final class ObjectFingerprintGenerator implements CommandFingerprintGenerator
{
    public function generate(object $command): string
    {
        $seen = [];

        return hash('sha256', encode($this->normalize($command, $seen)));
    }

    private function normalize(mixed $value, array &$seen): mixed
    {
        if ($value instanceof UnitEnum) {
            return $value::class . '::' . $value->name;
        }

        if (is_array($value)) {
            if (array_is_list($value)) {
                foreach ($value as $index => $item) {
                    $value[$index] = $this->normalize($item, $seen);
                }

                return $value;
            }

            ksort($value);

            foreach ($value as $key => $item) {
                $value[$key] = $this->normalize($item, $seen);
            }

            return $value;
        }

        if (is_object($value)) {
            $objectId = spl_object_id($value);

            if (isset($seen[$objectId])) {
                throw new RuntimeException('Circular reference detected while generating command fingerprint.');
            }

            $seen[$objectId] = true;
            $normalized = ['@class' => $value::class];

            foreach ((array) $value as $property => $item) {
                $normalized[$this->normalizePropertyName($property)] = $this->normalize($item, $seen);
            }

            unset($seen[$objectId]);

            ksort($normalized);

            return $normalized;
        }

        if (is_resource($value)) {
            return get_resource_type($value);
        }

        return $value;
    }

    private function normalizePropertyName(string $property): string
    {
        if (! str_contains($property, "\0")) {
            return $property;
        }

        $chunks = explode("\0", $property);

        return $chunks[count($chunks) - 1];
    }
}
