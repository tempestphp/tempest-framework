<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Mapper;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Container\GenericContainer;
use Tempest\Mapper\CasterFactory;
use Tempest\Mapper\Casters\BooleanCaster;
use Tempest\Mapper\Casters\FloatCaster;
use Tempest\Mapper\Casters\IntegerCaster;
use Tempest\Mapper\Casters\NativeDateTimeCaster;
use Tempest\Mapper\MapperCache;
use Tempest\Mapper\MapperConfig;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Mapper\ObjectFactory;
use Tests\Tempest\Benchmark\Mapper\Fixtures\BenchmarkBook;

final class ObjectHydrationBench
{
    private GenericContainer $container;

    private MapperConfig $config;

    private ObjectFactory $factory;

    /** @var array<int, array<string, mixed>> */
    private array $rows;

    public function setUp(): void
    {
        $this->container = new GenericContainer();

        // Casters are normally registered through discovery. The benchmark registers the ones the
        // fixture needs so that it hydrates the same way an application would.
        $casters = new CasterFactory($this->container);

        foreach ([BooleanCaster::class, IntegerCaster::class, FloatCaster::class, NativeDateTimeCaster::class] as $caster) {
            $casters->addCaster($caster);
        }

        $this->container->singleton(CasterFactory::class, $casters);
        $this->config = new MapperConfig([ArrayToObjectMapper::class, ObjectToArrayMapper::class]);
        $this->factory = new ObjectFactory($this->config, $this->container, new MapperCache());

        $this->rows = array_map(
            static fn (int $i) => [
                'id' => $i,
                'title' => "Book {$i}",
                'description' => "Description {$i}",
                'published' => ($i % 2) === 0,
                'price' => $i / 3,
                'created_at' => '2025-01-01 00:00:00',
            ],
            range(1, 5000),
        );
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchHydrateObject(): void
    {
        $this->factory->withData($this->rows[0])->to(BenchmarkBook::class);
    }

    #[BeforeMethods('setUp')]
    #[ParamProviders('provideCollectionSizes')]
    #[Iterations(5)]
    #[Revs(20)]
    #[Warmup(2)]
    public function benchHydrateCollection(array $params): void
    {
        $this->factory
            ->withData(array_slice($this->rows, 0, $params['size']))
            ->collection()
            ->to(BenchmarkBook::class);
    }

    public function provideCollectionSizes(): iterable
    {
        yield '10 rows' => ['size' => 10];
        yield '100 rows' => ['size' => 100];
        yield '1000 rows' => ['size' => 1000];
        yield '5000 rows' => ['size' => 5000];
    }

    /**
     * Resolves the mappers from the container on every operation, as happened before they were
     * cached. Compare against {@see self::benchHydrateObject()}, which reuses a warm cache.
     */
    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchMapWithColdMapperCache(): void
    {
        new ObjectFactory($this->config, $this->container, new MapperCache())
            ->withData($this->rows[0])
            ->to(BenchmarkBook::class);
    }
}
