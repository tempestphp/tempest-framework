<?php

namespace Tempest\Upgrade\Tests\Tempest3;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

#[RunTestsInSeparateProcesses]
final class Tempest3RectorTest extends TestCase
{
    private RectorTester $rector {
        get => new RectorTester(__DIR__ . '/tempest30_rector.php');
    }

    #[Test]
    public function map_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/MapNamespaceChange.input.php')
            ->assertContains('use function Tempest\Mapper\map;')
            ->assertNotContains('use function Tempest\map;');
    }

    #[Test]
    public function make_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/MakeNamespaceChange.input.php')
            ->assertContains('use function Tempest\Mapper\make;')
            ->assertNotContains('use function Tempest\make;');
    }

    #[Test]
    public function fully_qualified_map_call(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedMapCall.input.php')
            ->assertContains('use Tempest\Mapper\map;')
            ->assertContains('return map($data)->to(Author::class);');
    }

    #[Test]
    public function fully_qualified_make_call(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedMakeCall.input.php')
            ->assertContains('use Tempest\Mapper\make;')
            ->assertContains('return make(Author::class)');
    }

    #[Test]
    public function exception_processor_to_exception_reporter_fully_qualified(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ExceptionProcessorFullyQualified.input.php')
            ->assertContains('use Tempest\Core\Exceptions\ExceptionReporter;')
            ->assertContains('implements \Tempest\Core\Exceptions\ExceptionReporter')
            ->assertContains('public function report(')
            ->assertNotContains('use Tempest\Core\ExceptionProcessor')
            ->assertNotContains('public function process(');
    }

    #[Test]
    public function exception_processor_to_exception_reporter_with_constructor(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ExceptionProcessorWithConstructor.input.php')
            ->assertContains('use Tempest\Core\Exceptions\ExceptionReporter;')
            ->assertContains('implements \Tempest\Core\Exceptions\ExceptionReporter')
            ->assertContains('public function report(')
            ->assertNotContains('use Tempest\Core\ExceptionProcessor')
            ->assertNotContains('public function process(');
    }

    #[Test]
    public function exception_processor_to_exception_reporter_imported_only(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ExceptionProcessorImportedOnly.input.php')
            ->assertContains('use Tempest\Core\Exceptions\ExceptionReporter;')
            ->assertContains('implements \Tempest\Core\Exceptions\ExceptionReporter')
            ->assertContains('public function report(')
            ->assertNotContains('use ExceptionProcessor')
            ->assertNotContains('public function process(');
    }

    #[Test]
    public function has_context_to_provides_context_fully_qualified(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/HasContextFullyQualified.input.php')
            ->assertContains('use Tempest\Core\ProvidesContext;')
            ->assertContains('implements \Tempest\Core\ProvidesContext')
            ->assertNotContains('use Tempest\Core\HasContext');
    }

    #[Test]
    public function view_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ViewNamespaceChange.input.php')
            ->assertContains('use function Tempest\View\view;')
            ->assertNotContains('use function Tempest\view;');
    }

    #[Test]
    public function fully_qualified_view_call(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedViewCall.input.php')
            ->assertContains('use Tempest\View\view;')
            ->assertContains('return view($template);');
    }

    #[Test]
    public function map_iterable_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/MapIterableNamespaceChange.input.php')
            ->assertContains('use function Tempest\Support\Arr\map;')
            ->assertNotContains('use function Tempest\Support\Arr\map_iterable;');
    }

    #[Test]
    public function fully_qualified_map_iterable_call(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedMapIterableCall.input.php')
            ->assertContains('use Tempest\Support\Arr\map;')
            ->assertContains('return map($data, fn ($item) => $item * 2);');
    }

    #[Test]
    public function bindable_resolve_return_type_becomes_nullable(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/BindableResolveReturnType.input.php')
            ->assertContains('public static function resolve(string $input): ?static')
            ->assertNotContains('public static function resolve(string $input): self');
    }
}
