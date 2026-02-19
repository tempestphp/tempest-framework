<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Closure;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Mapper\Exceptions\DataCouldNotBeMapped;
use Tempest\Mapper\Exceptions\MapperWasMissing;
use Tempest\Mapper\Mappers\ArrayToJsonMapper;
use Tempest\Mapper\Mappers\JsonToArrayMapper;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Mapper\Mappers\ObjectToJsonMapper;
use Tempest\Reflection\FunctionReflector;
use Tempest\Support\Arr;
use Tempest\Support\Json;
use UnitEnum;

/** @template ClassType */
#[Singleton]
final class ObjectFactory
{
    private mixed $from;

    private mixed $to;

    private array $with = [];

    private bool $isCollection = false;

    private Context|UnitEnum|string|null $context = null;

    /** @var \Tempest\Mapper\Mapper[] */
    private array $mappers;

    public function __construct(
        private readonly MapperConfig $config,
        private readonly Container $container,
    ) {
        $this->mappers = $this->resolveMappers();
    }

    /**
     * Sets the target class for mapping operations.
     *
     * ### Example
     * ```php
     * $factory->forClass(Author::class)->from(['name' => 'Jon Doe']);
     * ```
     *
     * @template T of object
     * @param T|class-string<T> $objectOrClass
     * @return self<T>
     */
    public function forClass(mixed $objectOrClass): self
    {
        $this->to = $objectOrClass;

        return $this;
    }

    /**
     * Sets the source data for mapping.
     *
     * ### Example
     * ```php
     * $factory->withData(['name' => 'Jon Doe'])->to(Author::class);
     * ```
     */
    public function withData(mixed $data): self
    {
        $this->from = $data;

        return clone $this;
    }

    /**
     * Marks the mapping operation to process an array of objects instead of a single object.
     *
     * ### Example
     * ```php
     * make(Author::class)
     *     ->collection()
     *     ->from([
     *         ['name' => 'Jon Doe'],
     *         ['name' => 'Jane Smith'],
     *     ]);
     * ```
     *
     * @return self<ClassType[]>
     */
    public function collection(): self
    {
        $this->isCollection = true;

        return $this;
    }

    /**
     * Sets the context for mapping, allowing context-specific mappers to be used.
     *
     * ### Example
     * ```php
     * make(Author::class)
     *     ->in(Context::API)
     *     ->from(['name' => 'Jon Doe']);
     * ```
     *
     * @return self<ClassType>
     */
    public function in(Context|UnitEnum|string|null $context): self
    {
        $clone = clone($this, [
            'context' => $context,
        ]);

        $clone->mappers = $clone->resolveMappers();

        return $clone;
    }

    /**
     * Maps the given data to the target class.
     *
     * ### Example
     * ```php
     * $author = make(Author::class)->from([
     *     'first_name' => 'Jon',
     *     'last_name' => 'Doe',
     * ]);
     * ```
     *
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
     * Specifies custom mappers to use for the mapping operation.
     *
     * ### Example
     * ```php
     * map(['name' => 'Jon Doe'])
     *     ->with(CustomMapper::class)
     *     ->to(Author::class);
     *
     * map($data)
     *     ->with(fn (SomeMapper $mapper) => $mapper->map($data))
     *     ->do();
     * ```
     *
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
     * Maps the source data to the specified target class.
     *
     * ### Example
     * ```php
     * $author = map([
     *     'first_name' => 'Jon',
     *     'last_name' => 'Doe',
     * ])->to(Author::class);
     * ```
     *
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

    /**
     * Executes the mapping using explicitly specified mappers.
     *
     * ### Example
     * ```php
     * $result = map($data)
     *     ->with(ObjectToArrayMapper::class)
     *     ->with(ArrayToJsonMapper::class)
     *     ->do();
     * ```
     */
    public function do(): mixed
    {
        if ($this->with === []) {
            throw new MapperWasMissing();
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

    /**
     * Converts the source data to an array.
     *
     * ### Example
     * ```php
     * $array = map($author)->toArray();
     * $arrays = map($authors)->collection()->toArray();
     * ```
     */
    public function toArray(): array
    {
        if (is_object($this->from)) {
            return $this->with(ObjectToArrayMapper::class)->do();
        }

        if (is_array($this->from)) {
            if (! $this->isCollection) {
                return $this->from;
            }

            return Arr\map_with_keys(
                array: $this->from,
                map: fn (mixed $item, mixed $key) => yield $key => $this->withData($item)->toArray(),
            );
        }

        if (Json\is_valid($this->from)) {
            return $this->with(JsonToArrayMapper::class)->do();
        }

        throw new DataCouldNotBeMapped($this->from, 'array');
    }

    /**
     * Converts the source data to a JSON string.
     *
     * ### Example
     * ```php
     * $json = map($author)->toJson();
     * $json = map(['name' => 'Jon Doe'])->toJson();
     * ```
     */
    public function toJson(): string
    {
        if (is_object($this->from)) {
            return $this->with(ObjectToJsonMapper::class)->do();
        }

        if (is_array($this->from)) {
            return $this->with(ArrayToJsonMapper::class)->do();
        }

        throw new DataCouldNotBeMapped($this->from, 'json');
    }

    /**
     * Maps data from one format to another.
     *
     * ### Example
     * ```php
     * $author = $factory->map(['name' => 'Jon Doe'], to: Author::class);
     * ```
     *
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

    private function mapObject(mixed $from, mixed $to, bool $isCollection): mixed
    {
        // Map collections
        if ($isCollection && is_array($from)) {
            return array_map(fn (mixed $item) => $this->mapObject(
                from: $item,
                to: $to,
                isCollection: false,
            ), $from);
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

        $context = MappingContext::from($this->context);

        foreach ($this->mappers as $mapper) {
            if ($mapper->canMap($from, $to)) {
                return $mapper->map($from, $to);
            }
        }

        throw new DataCouldNotBeMapped($from, $to);
    }

    /**
     * @template MapperType of \Tempest\Mapper\Mapper
     * @param Closure(MapperType $mapper, mixed $from): mixed|class-string<\Tempest\Mapper\Mapper> $mapper
     */
    private function mapWith(mixed $mapper, mixed $from, mixed $to): mixed
    {
        $context = MappingContext::from($this->context);

        if ($mapper instanceof Closure) {
            $function = new FunctionReflector($mapper);

            $data = [
                'from' => $from,
            ];

            foreach ($function->getParameters() as $parameter) {
                if ($parameter->getType()->matches(Context::class)) {
                    $data[$parameter->getName()] ??= $context;
                    continue;
                }

                $data[$parameter->getName()] ??= $this->container->get($parameter->getType()->getName(), context: $context);
            }

            return $mapper(...$data);
        }

        $mapper = $this->container->get($mapper, context: $context);

        /** @var Mapper $mapper */
        return $mapper->map($from, $to);
    }

    /**
     * We cache mapper instances within the factory so that we prevent mappers being resolved on every mapping call.
     * Whenever a mapping context changes, we'll have to re-resolve the mapper classes with the new context.
     */
    private function resolveMappers(): array
    {
        /** @var Mapper[] $mappers */
        $mappers = [];

        $context = MappingContext::from($this->context);

        foreach ($this->config->mappers as $mapperClass) {
            $mappers[] = $this->container->get($mapperClass, context: $context);
        }

        return $mappers;
    }
}
