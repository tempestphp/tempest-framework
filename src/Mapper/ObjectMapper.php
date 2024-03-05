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

    /** @var \Tempest\Mapper\Mapper[] */
    private readonly array $mappers;

    public function __construct()
    {
        $this->mappers = [
            new PsrRequestToRequestMapper(),
            new RequestToPsrRequestMapper(),
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
        $this->isCollection = true;

        return $this;
    }

    /**
     * @return ClassType
     */
    public function from(mixed $data): array|object
    {
        return $this->map($this->objectOrClass, $data, $this->isCollection);
    }

    /**
     * @template T of object
     * @param T|class-string<T> $objectOrClass
     * @return T
     */
    public function to(object|string $objectOrClass): object
    {
        return $this->map($objectOrClass, $this->data, $this->isCollection);
    }

    private function map(
        object|string $to,
        mixed $from,
        bool $isCollection,
    ): array|object {
        if ($isCollection && is_array($from)) {
            return array_map(
                fn (mixed $item) => $this->map(
                    to: $to,
                    from: $item,
                    isCollection: false
                ),
                $from,
            );
        }

        foreach ($this->mappers as $mapper) {
            if ($mapper->canMap($to, $from)) {
                return $mapper->map($to, $from);
            }
        }

        throw new CannotMapDataException($to, $from);
    }
}
