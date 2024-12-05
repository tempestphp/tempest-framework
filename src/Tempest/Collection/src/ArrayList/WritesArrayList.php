<?php

declare(strict_types=1);

namespace Tempest\Collection\ArrayList;

/**
 * @template TValue
 */
trait WritesArrayList
{
    /**
     * @var array<int,TValue>
     */
    private array $items = [];

    /**
     * @param TValue $value
     * @return self<TValue>
     */
    public function add($value): self
    {
        $this->items[] = $value;

        return $this;
    }

    /**
     * @return self<TValue>
     */
    public function insert(int $index, $value): self
    {
        $this->items[$index] = $value;

        return $this;
    }

    /**
     * Removes the first occurrence of a specific object from the ArrayList<TValue>.
     *
     * @param TValue $value
     * @return self<TValue>
     */
    public function remove($value): self
    {
        $index = array_search($value, $this->items, true);

        if ($index !== false) {
            $this->removeAt($index);
        }

        return $this;
    }

    /**
     * Removes all occurrences of a specific object from the ArrayList<TValue>.
     *
     * @param TValue $value
     * @return self<TValue>
     */
    public function removeAll($value): self
    {
        $this->items = array_values(
            array_filter(
                $this->items,
                static function ($item) use ($value): bool {
                    return $item !== $value;
                },
            ),
        );

        return $this;
    }

    /**
     * @return self<TValue>
     */
    public function removeAt(int $index): self
    {
        unset($this->items[$index]);

        return $this;
    }

    /**
     * @return self<TValue>
     */
    public function clear(): self
    {
        $this->items = [];

        return $this;
    }

    /**
     * @param int $offset
     * @param TValue $value
     */
    public function offsetSet(mixed $offset, $value): void
    {
        $this->insert($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->removeAt($offset);
    }
}
