<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\ORM\Exceptions\CannotMapDataException;

/* @template ClassType */
final class ObjectMapper
{
    private object|string $objectOrClass;

    private mixed $data;

    /** @var \Tempest\Interfaces\Mapper[] */
    private readonly array $mappers;

    public function __construct()
    {
        $this->mappers = [
            new ArrayToObjectMapper(),
            new QueryToModelMapper(),
            new ModelToQueryMapper(),
            new RequestToObjectMapper(),
        ];
    }

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
        return $this;
    }

    /**
     * @return ClassType
     */
    public function from(mixed $data): array|object
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->canMap($this->objectOrClass, $data)) {
                return $mapper->map($this->objectOrClass, $data);
            }
        }

        throw new CannotMapDataException();
    }

    /**
     * @template T of object
     * @param T|class-string<T> $objectOrClass
     * @return T
     */
    public function to(object|string $objectOrClass): object
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->canMap($objectOrClass, $this->data)) {
                return $mapper->map($objectOrClass, $this->data);
            }
        }

        throw new CannotMapDataException();
    }
}
