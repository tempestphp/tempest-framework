<?php

declare(strict_types=1);

namespace Tempest\Http;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use LogicException;
use Traversable;

final readonly class RequestHeaders implements ArrayAccess, IteratorAggregate
{
    /**
     * @param array<string, string> $headers
     */
    public static function normalizeFromArray(array $headers): self
    {
        $normalized = array_combine(
            array_map(strtolower(...), array_keys($headers)),
            array_values($headers),
        );
        return new self($normalized);
    }

    /** @param array<string, string> $headers */
    private function __construct(
        private array $headers = [],
    ) {}

    public function offsetExists(mixed $offset): bool
    {
        $offset = strtolower($offset);

        return isset($this->headers[$offset]);
    }

    public function offsetGet(mixed $offset): string
    {
        return $this->get((string) $offset);
    }

    public function get(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function has(string $name): bool
    {
        return (bool) $this->get($name);
    }

    public function getHeader(string $name): Header
    {
        return new Header(strtolower($name), array_filter([$this->get($name)]));
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('Unable to alter request headers.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('Unable to alter request headers.');
    }

    public function toArray(): array
    {
        return $this->headers;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->headers);
    }
}
