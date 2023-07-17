<?php

declare(strict_types=1);

namespace Tempest\ORM;

use Tempest\ORM\Exceptions\CannotMapDataException;
use Tempest\ORM\Mappers\ArrayMapper;
use Tempest\ORM\Mappers\QueryMapper;
use Tempest\ORM\Mappers\SqlMapper;

/* @template ClassType */
final class ObjectFactory
{
    private object|string $objectOrClass;

    private mixed $data;

    /** @var \Tempest\Interfaces\Mapper[] */
    private readonly array $mappers;

    public function __construct()
    {
        $this->mappers = [
            new ArrayMapper(),
            new SqlMapper(),
            new QueryMapper(),
        ];
    }

    /**
     * @template T
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
     * @template T
     * @param T|class-string<T> $object
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
