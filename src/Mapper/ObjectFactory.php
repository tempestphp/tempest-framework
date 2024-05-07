<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Container\Container;
use Tempest\Mapper\Exceptions\CannotMapDataException;

/* @template ClassType */
final class ObjectFactory
{
    private object|string $objectOrClass;

    private mixed $data;

    private bool $isCollection = false;

    public function __construct(
        private readonly MapperConfig $config,
        private readonly Container $container,
    ) {
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
        return $this->mapObject(
            from: $data,
            to: $this->objectOrClass,
            isCollection: $this->isCollection,
        );
    }

    /**
     * @template T of object
     * @param T|class-string<T> $objectOrClass
     * @return T|T[]
     */
    public function to(object|string $objectOrClass): array|object
    {
        return $this->mapObject(
            from: $this->data,
            to: $objectOrClass,
            isCollection: $this->isCollection,
        );
    }

    /**
     * @template T of object
     * @param mixed $from
     * @param T|class-string<T> $objectOrClass
     * @return T
     */
    public function map(mixed $from, object|string $to): array|object
    {
        return $this->mapObject(
            from: $from,
            to: $to,
            isCollection: $this->isCollection,
        );
    }

    private function mapObject(
        mixed $from,
        object|string $to,
        bool $isCollection,
    ): array|object {
        if ($isCollection && is_array($from)) {
            return array_map(
                fn (mixed $item) => $this->mapObject(
                    from: $item,
                    to: $to,
                    isCollection: false,
                ),
                $from,
            );
        }

        foreach ($this->config->mappers as $mapperClass) {
            /** @var Mapper $mapper */
            $mapper = $this->container->get($mapperClass);

            if ($mapper->canMap(from: $from, to: $to)) {
                return $mapper->map(from: $from, to: $to);
            }
        }

        throw new CannotMapDataException($from, $to);
    }
}
