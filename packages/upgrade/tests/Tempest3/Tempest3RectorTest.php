<?php

namespace Tempest\Upgrade\Tests\Tempest3;

use PHPUnit\Framework\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

final class Tempest3RectorTest extends TestCase
{
    private RectorTester $rector {
        get => new RectorTester(__DIR__ . '/tempest30_rector.php');
    }

    public function test_map_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/MapNamespaceChange.input.php')
            ->assertContains('use function Tempest\Mapper\map;')
            ->assertNotContains('use function Tempest\map;');
    }

    public function test_make_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/MakeNamespaceChange.input.php')
            ->assertContains('use function Tempest\Mapper\make;')
            ->assertNotContains('use function Tempest\make;');
    }

    public function test_fully_qualified_map_call(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedMapCall.input.php')
            ->assertContains('use Tempest\Mapper\map;')
            ->assertContains('return map($data)->to(Author::class);');
    }

    public function test_fully_qualified_make_call(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedMakeCall.input.php')
            ->assertContains('use Tempest\Mapper\make;')
            ->assertContains('return make(Author::class)');
    }

    public function test_exception_processor_to_exception_reporter_fully_qualified(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ExceptionProcessorFullyQualified.input.php')
            ->assertContains('use Tempest\Core\Exceptions\ExceptionReporter;')
            ->assertContains('implements \Tempest\Core\Exceptions\ExceptionReporter')
            ->assertContains('public function report(')
            ->assertNotContains('use Tempest\Core\ExceptionProcessor')
            ->assertNotContains('public function process(');
    }

    public function test_exception_processor_to_exception_reporter_with_constructor(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ExceptionProcessorWithConstructor.input.php')
            ->assertContains('use Tempest\Core\Exceptions\ExceptionReporter;')
            ->assertContains('implements \Tempest\Core\Exceptions\ExceptionReporter')
            ->assertContains('public function report(')
            ->assertNotContains('use Tempest\Core\ExceptionProcessor')
            ->assertNotContains('public function process(');
    }

    public function test_exception_processor_to_exception_reporter_imported_only(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ExceptionProcessorImportedOnly.input.php')
            ->assertContains('use Tempest\Core\Exceptions\ExceptionReporter;')
            ->assertContains('implements \Tempest\Core\Exceptions\ExceptionReporter')
            ->assertContains('public function report(')
            ->assertNotContains('use ExceptionProcessor')
            ->assertNotContains('public function process(');
    }

    public function test_has_context_to_provides_context_fully_qualified(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/HasContextFullyQualified.input.php')
            ->assertContains('use Tempest\Core\ProvidesContext;')
            ->assertContains('implements \Tempest\Core\ProvidesContext')
            ->assertNotContains('use Tempest\Core\HasContext');
    }

    public function test_view_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ViewNamespaceChange.input.php')
            ->assertContains('use function Tempest\View\view;')
            ->assertNotContains('use function Tempest\view;');
    }

    public function test_fully_qualified_view_call(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedViewCall.input.php')
            ->assertContains('use Tempest\View\view;')
            ->assertContains('return view($template);');
    }

    public function test_map_iterable_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/MapIterableNamespaceChange.input.php')
            ->assertContains('use function Tempest\Support\Arr\map;')
            ->assertNotContains('use function Tempest\Support\Arr\map_iterable;');
    }

    public function test_fully_qualified_map_iterable_call(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedMapIterableCall.input.php')
            ->assertContains('use Tempest\Support\Arr\map;')
            ->assertContains('return map($data, fn ($item) => $item * 2);');
    }
}
