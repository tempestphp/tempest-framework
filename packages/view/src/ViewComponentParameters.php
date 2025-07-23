<?php

declare(strict_types=1);

namespace Tempest\View;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use IteratorAggregate;
use JsonSerializable;

final class ViewComponentParameters implements ArrayAccess, IteratorAggregate, Countable, JsonSerializable
{
    /** @var ViewComponentParameter[] */
    private readonly array $parameters;

    public function __construct(ViewComponentParameter ...$parameters)
    {
        $this->parameters = $parameters;
    }

    /** @var ViewComponentParameter[] */
    public array $required {
        get => array_filter($this->parameters, static fn (ViewComponentParameter $parameter) => $parameter->required);
    }

    /** @var ViewComponentParameter[] */
    public array $optional {
        get => array_filter($this->parameters, static fn (ViewComponentParameter $parameter) => ! $parameter->required);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->parameters[$offset]);
    }

    public function offsetGet(mixed $offset): ?ViewComponentParameter
    {
        return $this->parameters[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('ViewComponentParameters is readonly');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('ViewComponentParameters is readonly');
    }

    /**
     * @return ArrayIterator<ViewComponentParameter>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->parameters);
    }

    public function count(): int
    {
        return count($this->parameters);
    }

    /** @return ViewComponentParameter[] */
    public function toArray(): array
    {
        return $this->parameters;
    }

    public function getByName(string $name): ?ViewComponentParameter
    {
        return array_find($this->parameters, static fn (ViewComponentParameter $parameter) => $parameter->name === $name);
    }

    public function hasParameter(string $name): bool
    {
        return $this->getByName($name) !== null;
    }

    public function jsonSerialize(): array
    {
        return $this->parameters;
    }
}
