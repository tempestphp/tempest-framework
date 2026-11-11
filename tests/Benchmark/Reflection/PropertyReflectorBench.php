<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Reflection;

use Generator;
use PhpBench\Attributes\AfterMethods;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Tempest\Reflection\PropertyReflector;
use Tests\Tempest\Benchmark\Reflection\Fixtures\Book;
use Tests\Tempest\Benchmark\Reflection\Fixtures\Fqcn\FqcnModels;
use Tests\Tempest\Benchmark\Reflection\Fixtures\Plain\PlainModels;
use Tests\Tempest\Benchmark\Reflection\Fixtures\Short\ShortNameModels;
use Throwable;

#[Iterations(20)]
final class PropertyReflectorBench
{
    private const int COLD_REVS = 20;

    private PropertyReflector $reflector;

    private ?string $expectedType;

    /** @var list<PropertyReflector> */
    private array $coldReflectors = [];

    /** @var list<string> */
    private array $coldFiles = [];

    private int $coldIndex = 0;

    /** @param array{class: class-string, property: string, type: string|null} $params */
    public function setUpWarm(array $params): void
    {
        $this->reflector = PropertyReflector::fromParts($params['class'], $params['property']);
        $this->expectedType = $params['type'];
    }

    #[BeforeMethods('setUpWarm')]
    #[AfterMethods('assertWarmType')]
    #[ParamProviders('warmSources')]
    #[Revs(50_000)]
    #[Warmup(5_000)]
    public function benchWarmLookup(): void
    {
        $this->reflector->getIterableType();
    }

    /** @return Generator<string, array{class: class-string, property: string, type: string|null}> */
    public function warmSources(): Generator
    {
        yield 'short-name' => ['class' => ShortNameModels::class, 'property' => 'one', 'type' => Book::class];
        yield 'fqcn' => ['class' => FqcnModels::class, 'property' => 'one', 'type' => Book::class];
        yield 'int[]' => ['class' => PlainModels::class, 'property' => 'integers', 'type' => 'int'];
        yield 'list<string>' => ['class' => PlainModels::class, 'property' => 'strings', 'type' => 'string'];
        yield 'no-docblock' => ['class' => PlainModels::class, 'property' => 'noDocblock', 'type' => null];
    }

    public function assertWarmType(): void
    {
        Assert::assertSame($this->expectedType, $this->reflector->getIterableType()?->getName());
    }

    // Cold means uncached source metadata, not an empty OS filesystem cache.
    #[BeforeMethods('setUpFirstLookup')]
    #[AfterMethods('tearDownCold')]
    #[ParamProviders('firstLookupSources')]
    #[Revs(self::COLD_REVS)]
    #[Warmup(0)]
    public function benchFirstLookup(): void
    {
        $this->coldReflectors[$this->coldIndex++]->getIterableType();
    }

    /** @return Generator<string, array{extraProperties: int, sourceCached: bool, groupedImport: bool, propertiesPerFile: int}> */
    public function firstLookupSources(): Generator
    {
        yield 'cold-small' => ['extraProperties' => 0, 'sourceCached' => false, 'groupedImport' => false, 'propertiesPerFile' => 1];
        yield 'cold-100-properties' => ['extraProperties' => 100, 'sourceCached' => false, 'groupedImport' => false, 'propertiesPerFile' => 1];
        yield 'cached-source' => ['extraProperties' => 0, 'sourceCached' => true, 'groupedImport' => false, 'propertiesPerFile' => 1];
        yield 'cold-grouped-import' => ['extraProperties' => 0, 'sourceCached' => false, 'groupedImport' => true, 'propertiesPerFile' => 1];
        yield 'cold-20-properties-one-file' => ['extraProperties' => 0, 'sourceCached' => false, 'groupedImport' => false, 'propertiesPerFile' => self::COLD_REVS];
    }

    /** @param array{extraProperties: int, sourceCached: bool, groupedImport: bool, propertiesPerFile: int} $params */
    public function setUpFirstLookup(array $params): void
    {
        // Load resolver dependencies without touching any of the measured source files.
        PropertyReflector::fromParts(ShortNameModels::class, 'one')->getIterableType();
        $run = 'TempestParseBench' . bin2hex(random_bytes(8));

        try {
            for ($i = 0; $i < self::COLD_REVS; $i += $params['propertiesPerFile']) {
                $file = tempnam(sys_get_temp_dir(), 'tempest-reflection-');

                if ($file === false) {
                    throw new RuntimeException('Could not create a cold benchmark source file.');
                }

                $this->coldFiles[] = $file;
                $namespace = $run . '\\Case' . $i;

                if (file_put_contents($file, $this->buildColdSource($namespace, $params['extraProperties'], $params['groupedImport'], $params['propertiesPerFile'])) === false) {
                    throw new RuntimeException('Could not write a cold benchmark source file.');
                }

                require $file;

                for ($property = 0; $property < $params['propertiesPerFile']; $property++) {
                    $this->coldReflectors[] = PropertyReflector::fromParts($namespace . '\\BooksHolder', $property === 0 ? 'books' : 'books' . $property);
                }
            }

            if ($params['sourceCached']) {
                foreach ($this->coldReflectors as $reflector) {
                    Assert::assertSame(Book::class, $reflector->getClass()->getProperty('warmupBooks')->getIterableType()?->getName());
                }
            }
        } catch (Throwable $exception) {
            $this->removeColdFiles();
            throw $exception;
        }
    }

    public function tearDownCold(): void
    {
        try {
            Assert::assertSame(self::COLD_REVS, $this->coldIndex, 'First-lookup subjects require exactly 20 revolutions and no warmup.');

            foreach ($this->coldReflectors as $reflector) {
                Assert::assertSame(Book::class, $reflector->getIterableType()?->getName());
            }
        } finally {
            $this->removeColdFiles();
        }
    }

    private function removeColdFiles(): void
    {
        foreach ($this->coldFiles as $file) {
            unlink($file);
        }

        $this->coldFiles = [];
        $this->coldReflectors = [];
        $this->coldIndex = 0;
    }

    private function buildColdSource(string $namespace, int $extraProperties, bool $groupedImport, int $propertiesPerFile): string
    {
        $bookClass = Book::class;
        $bookNamespace = substr($bookClass, 0, strrpos($bookClass, '\\'));
        $import = $groupedImport ? "use {$bookNamespace}\\{Book};" : "use {$bookClass};";
        $properties = '';

        for ($i = 0; $i < $extraProperties; $i++) {
            $properties .= "    public string \$field{$i};\n";
        }

        for ($i = 1; $i < $propertiesPerFile; $i++) {
            $properties .= "    /** @var Book[] */\n    public array \$books{$i};\n";
        }

        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        {$import}

        final class BooksHolder
        {
            /** @var Book[] */
            public array \$warmupBooks;

            /** @var Book[] */
            public array \$books;
        {$properties}
        }
        PHP;
    }
}
