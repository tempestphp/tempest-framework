<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Container\Container;
use Tempest\Mapper\Exceptions\CannotMapDataException;
use Tempest\Mapper\Exceptions\MissingMapperException;
use Tempest\Mapper\Mappers\ArrayToJsonMapper;
use Tempest\Mapper\Mappers\JsonToArrayMapper;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Mapper\Mappers\ObjectToJsonMapper;
use Tempest\Reflection\FunctionReflector;

/** @template ClassType */
final class ObjectFactory
{
    private mixed $from;

    private mixed $to;

    private array $with = [];

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
    public function forClass(mixed $objectOrClass): self
    {
        $this->to = $objectOrClass;

        return $this;
    }

    public function withData(mixed $data): self
    {
        $this->from = $data;

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
    public function from(mixed $data): mixed
    {
        return $this->mapObject(
            from: $data,
            to: $this->to,
            isCollection: $this->isCollection,
        );
    }

    /**
     * @template MapperType of \Tempest\Mapper\Mapper
     * @param Closure(MapperType $mapper, mixed $from): mixed|class-string<\Tempest\Mapper\Mapper> ...$mappers
     * @return self<ClassType>
     */
    public function with(Closure|string ...$mappers): self
    {
        $this->with = [...$this->with, ...$mappers];

        return $this;
    }

    /**
     * @template T of object
     * @param T|class-string<T>|string $to
     * @return T|T[]|mixed
     */
    public function to(mixed $to): mixed
    {
        return $this->mapObject(
            from: $this->from,
            to: $to,
            isCollection: $this->isCollection,
        );
    }

    public function do(): mixed
    {
        if ($this->with === []) {
            throw new MissingMapperException();
        }

        $result = $this->from;

        foreach ($this->with as $mapper) {
            $result = $this->mapWith(
                mapper: $mapper,
                from: $result,
                to: null,
            );
        }

        return $result;
    }

    public function toArray(): array
    {
        if (is_object($this->from)) {
            return $this->with(ObjectToArrayMapper::class)->do();
        }

        if (is_array($this->from)) {
            return $this->from;
        }

        if (is_string($this->from) && json_validate($this->from)) {
            return $this->with(JsonToArrayMapper::class)->do();
        }

        throw new CannotMapDataException($this->from, 'array');
    }

    public function toJson(): string
    {
        if (is_object($this->from)) {
            return $this->with(ObjectToJsonMapper::class)->do();
        }

        if (is_array($this->from)) {
            return $this->with(ArrayToJsonMapper::class)->do();
        }

        throw new CannotMapDataException($this->from, 'json');
    }

    /**
     * @template T of object
     * @param T|class-string<T>|string $to
     * @return T|mixed
     */
    public function map(mixed $from, mixed $to): mixed
    {
        return $this->mapObject(
            from: $from,
            to: $to,
            isCollection: $this->isCollection,
        );
    }

    private function mapObject(
        mixed $from,
        mixed $to,
        bool $isCollection,
    ): mixed {
        // Map collections
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

        // Map using explicitly defined mappers
        if ($this->with) {
            $result = $from;

            foreach ($this->with as $mapper) {
                $result = $this->mapWith(
                    mapper: $mapper,
                    from: $result,
                    to: $to,
                );
            }

            return $result;
        }

        // Map using an inferred mapper
        $mappers = $this->config->mappers;

        foreach ($mappers as $mapperClass) {
            /** @var Mapper $mapper */
            $mapper = $this->container->get($mapperClass);

            if ($mapper->canMap(from: $from, to: $to)) {
                return $mapper->map(from: $from, to: $to);
            }
        }

        throw new CannotMapDataException($from, $to);
    }

    /**
     * @template MapperType of \Tempest\Mapper\Mapper
     * @param Closure(MapperType $mapper, mixed $from): mixed|class-string<\Tempest\Mapper\Mapper> $mapper
     */
    private function mapWith(
        mixed $mapper,
        mixed $from,
        mixed $to,
    ): mixed {
        if ($mapper instanceof Closure) {
            $function = new FunctionReflector($mapper);

            $data = [
                'from' => $from,
            ];

            foreach ($function->getParameters() as $parameter) {
                $data[$parameter->getName()] ??= $this->container->get($parameter->getType()->getName());
            }

            return $mapper(...$data);
        }

        $mapper = $this->container->get($mapper);

        /** @var Mapper $mapper */
        return $mapper->map($from, $to);
    }
}
