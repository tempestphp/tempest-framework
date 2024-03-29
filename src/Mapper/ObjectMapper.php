<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\ORM\Exceptions\CannotMapDataException;

/* @template ClassType */
final class ObjectMapper
{
    private object|string $objectOrClass;

    private mixed $data;

    private bool $isCollection = false;

    public function __construct(
        /** @var \Tempest\Mapper\Mapper[] */
        private readonly array $mappers = [],
    ) {}

    /**
     * @template T of object
     * @param T|class-string<T> $objectOrClass
     * @return self<T>
     */
    public function forClass(object|string $objectOrClass): self
    {
        $this->objectOrClass = $objectOrClass;

        return $this;
    }

    public function withData(mixed $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return self<ClassType[]>
     */
    public function collection(): self
    {
        $this->isCollection = true;

        return $this;
    }

    /**
     * @return ClassType
     */
    public function from(mixed $data): array|object
    {
        return $this->map(
            from: $data,
            to: $this->objectOrClass,
            isCollection: $this->isCollection,
        );
    }

    /**
     * @template T of object
     * @param T|class-string<T> $objectOrClass
     * @return T
     */
    public function to(object|string $objectOrClass): object
    {
        return $this->map(
            from: $this->data,
            to: $objectOrClass,
            isCollection: $this->isCollection,
        );
    }

    private function map(
        mixed $from,
        object|string $to,
        bool $isCollection,
    ): array|object {
        if ($isCollection && is_array($from)) {
            return array_map(
                fn (mixed $item) => $this->map(
                    from: $item,
                    to: $to,
                    isCollection: false,
                ),
                $from,
            );
        }

        foreach ($this->mappers as $mapper) {
            if ($mapper->canMap(from: $from, to: $to)) {
                return $mapper->map(from: $from, to: $to);
            }
        }
dd($this->mappers);
        throw new CannotMapDataException($from, $to);
    }
}
