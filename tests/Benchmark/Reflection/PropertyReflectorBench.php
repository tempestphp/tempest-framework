<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Reflection;

use PhpBench\Attributes\AfterMethods;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use ReflectionClass;
use ReflectionProperty;
use Tempest\Reflection\PropertyReflector;
use Tests\Tempest\Benchmark\Reflection\Fixtures\Book;
use Tests\Tempest\Benchmark\Reflection\Fixtures\Fqcn\FqcnModels;
use Tests\Tempest\Benchmark\Reflection\Fixtures\Plain\PlainModels;
use Tests\Tempest\Benchmark\Reflection\Fixtures\Short\ShortNameModels;

final class PropertyReflectorBench
{
    /** @var PropertyReflector[] */
    private array $shortNameReflectors;

    /** @var PropertyReflector[] */
    private array $fqcnReflectors;

    /** @var PropertyReflector[] */
    private array $builtinReflectors;

    /** @var array<int, string> */
    private array $coldFiles = [];

    public function setUp(): void
    {
        $this->shortNameReflectors = $this->collectReflectors(ShortNameModels::class);
        $this->fqcnReflectors = $this->collectReflectors(FqcnModels::class);
        $this->builtinReflectors = $this->collectReflectors(PlainModels::class);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(20)]
    #[Revs(50_000)]
    #[Warmup(5_000)]
    public function benchShortNameDocblocks(): void
    {
        foreach ($this->shortNameReflectors as $reflector) {
            $reflector->getIterableType();
        }
    }

    #[BeforeMethods('setUp')]
    #[Iterations(20)]
    #[Revs(50_000)]
    #[Warmup(5_000)]
    public function benchFqcnDocblocks(): void
    {
        foreach ($this->fqcnReflectors as $reflector) {
            $reflector->getIterableType();
        }
    }

    #[BeforeMethods('setUp')]
    #[Iterations(20)]
    #[Revs(50_000)]
    #[Warmup(5_000)]
    public function benchBuiltinTypeDocblocks(): void
    {
        foreach ($this->builtinReflectors as $reflector) {
            $reflector->getIterableType();
        }
    }

    /**
     * Measures the uncached parse path. Stream wrappers avoid tmp-file IO:
     * the source is served from memory via a unique `parse-bench://` wrapper.
     */
    #[BeforeMethods('setUpCold')]
    #[AfterMethods('tearDownCold')]
    #[Iterations(20)]
    #[Revs(20)]
    public function benchColdParse(): void
    {
        static $index = 0;

        $index++;

        $reflector = new PropertyReflector(new ReflectionProperty("TempestParseBench{$index}\\BooksHolder", 'books'));
        $reflector->getIterableType();

        // Prepare a fresh source for the next rev.
        $this->materializeColdFile($index + 1);
    }

    public function setUpCold(): void
    {
        // Rev n must hit a fresh file, and the class must already exist for
        // ReflectionProperty — so the file for rev n+1 is materialized during
        // rev n (and the first two here).
        for ($i = 1; $i <= 20; $i++) {
            $this->coldFiles[$i] = ColdParseStreamWrapper::pathFor('tempest-parse-bench-' . bin2hex(random_bytes(8)) . '.php');
        }

        $this->materializeColdFile(1);
        $this->materializeColdFile(2);
    }

    private function materializeColdFile(int $index): void
    {
        $file = $this->coldFiles[$index] ?? null;

        if ($file === null) {
            return;
        }

        ColdParseStreamWrapper::setContent($file, $this->buildColdSource($index));
        require_once $file;
    }

    public function tearDownCold(): void
    {
        $this->coldFiles = [];
    }

    private function buildColdSource(int $index): string
    {
        $bookClass = Book::class;

        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace TempestParseBench{$index};

        use {$bookClass};

        final class BooksHolder
        {
            /** @var Book[] */
            public array \$books;
        }
        PHP;
    }

    /**
     * @return PropertyReflector[]
     */
    private function collectReflectors(string $class): array
    {
        $reflectors = [];

        foreach (new ReflectionClass($class)->getProperties() as $property) {
            $reflectors[] = new PropertyReflector($property);
        }

        return $reflectors;
    }
}
