<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\Tests\Fixtures\DocblockCollectionModel;
use Tempest\Reflection\Tests\Fixtures\DocblockMemoizationModel;
use Tempest\Reflection\Tests\Fixtures\DocblockRelativeTarget;
use Tempest\Reflection\Tests\Fixtures\Imports\DocblockGroupTargetA;
use Tempest\Reflection\Tests\Fixtures\Imports\DocblockGroupTargetB;
use Tempest\Reflection\Tests\Fixtures\Imports\DocblockImportTarget;

/**
 * @internal
 */
final class PropertyReflectorTest extends TestCase
{
    #[Test]
    #[DataProvider('iterable_types')]
    public function iterable_types_are_consistent_across_reflectors(string $name, ?string $expected): void
    {
        $reflector = PropertyReflector::fromParts(DocblockMemoizationModel::class, $name);

        $this->assertSame($expected, $reflector->getIterableType()?->getName());
        $this->assertSame($expected, $reflector->getIterableType()?->getName());
        $this->assertSame($expected, PropertyReflector::fromParts(DocblockMemoizationModel::class, $name)->getIterableType()?->getName());
    }

    public static function iterable_types(): iterable
    {
        yield 'class name' => ['objects', 'stdClass'];
        yield 'builtin' => ['integers', 'int'];
        yield 'non-iterable docblock' => ['notIterable', null];
    }

    /** @param class-string $class */
    #[Test]
    #[DataProvider('docblock_contexts')]
    public function iterable_type_uses_the_docblocks_lexical_context(string $fixture, string $class, string $expected): void
    {
        require_once __DIR__ . '/Fixtures/' . $fixture . '.php';

        for ($i = 0; $i < 2; $i++) {
            $reflector = PropertyReflector::fromParts($class, 'items');
            $this->assertSame($expected, $reflector->getIterableType()?->getName());
        }
    }

    public static function docblock_contexts(): iterable
    {
        yield 'bracketed namespace' => ['DocblockNamespaces', 'DocblockBracketed\\First', DocblockImportTarget::class];
        yield 'other bracketed namespace' => ['DocblockNamespaces', 'DocblockOtherBracketed\\Second', DocblockGroupTargetB::class];
        yield 'global namespace block' => ['DocblockNamespaces', 'DocblockGlobalHolder', DocblockGroupTargetA::class];
        yield 'uppercase AS' => ['DocblockImports', 'DocblockImports\\UppercaseAlias', DocblockImportTarget::class];
        yield 'case-insensitive alias' => ['DocblockImports', 'DocblockImports\\LowercaseReference', DocblockImportTarget::class];
        yield 'grouped imports' => ['DocblockImports', 'DocblockImports\\Grouped', DocblockImportTarget::class];
        yield 'grouped namespace alias' => ['DocblockImports', 'DocblockImports\\GroupedPrefix', DocblockGroupTargetA::class];
        yield 'mixed grouped imports' => ['DocblockImports', 'DocblockImports\\MixedGrouped', DocblockGroupTargetB::class];
        yield 'separate function aliases' => ['DocblockImports', 'DocblockImports\\MixedGroupedDoesNotShadowClass', DocblockImportTarget::class];
        yield 'closure use and interpolation' => ['DocblockImports', 'DocblockImports\\AfterClosure', DocblockGroupTargetA::class];
        yield 'namespace-relative name' => ['DocblockImports', 'DocblockImports\\NamespaceRelative', 'DocblockImports\\NamespaceBook'];
        yield 'function import list' => ['DocblockImports', 'DocblockImports\\FunctionList', DocblockImportTarget::class];
        yield 'constant import list' => ['DocblockImports', 'DocblockImports\\ConstantList', DocblockGroupTargetA::class];
        yield 'literal brace in interpolated string' => ['DocblockImports', 'DocblockImports\\AfterString', DocblockGroupTargetB::class];
        yield 'second namespace' => ['DocblockImports', 'DocblockOtherImports\\OtherNamespace', DocblockGroupTargetB::class];
        yield 'nested trait' => ['DocblockTraits', 'DocblockTraitConsumer\\Holder', DocblockImportTarget::class];
        yield 'inherited trait property' => ['DocblockTraits', 'DocblockTraitConsumer\\Child', DocblockImportTarget::class];
        yield 'class overrides trait property' => ['DocblockTraits', 'DocblockTraitConsumer\\Override', DocblockGroupTargetB::class];
        yield 'promoted property overrides trait' => ['DocblockTraits', 'DocblockTraitConsumer\\PromotedOverride', DocblockGroupTargetB::class];
        yield 'readonly promoted property overrides trait' => ['DocblockTraits', 'DocblockTraitConsumer\\ReadonlyPromotedOverride', DocblockGroupTargetB::class];
    }

    #[Test]
    public function iterable_type_resolves_anonymous_classes_with_nested_arguments(): void
    {
        /** @var object $object */
        $object = require __DIR__ . '/Fixtures/DocblockAnonymous.php';

        $this->assertSame(DocblockImportTarget::class, PropertyReflector::fromParts($object, 'items')->getIterableType()?->getName());
    }

    #[Test]
    public function iterable_type_keeps_imports_local_to_repeated_namespace_blocks(): void
    {
        // PHPStan rejects these legal repeated aliases, so compile this regression fixture at runtime.
        $file = sys_get_temp_dir() . '/tempest-repeated-namespace-' . bin2hex(random_bytes(8)) . '.php';
        $namespace = 'DocblockRepeated' . bin2hex(random_bytes(8));
        $first = DocblockImportTarget::class;
        $second = DocblockGroupTargetB::class;

        file_put_contents($file, <<<PHP
        <?php

        namespace {$namespace} {
            use {$first} as Book;

            class First {
                /** @var Book[] */
                public array \$items;
            }
        }

        namespace {$namespace} {
            use {$second} as Book;

            class Second {
                /** @var Book[] */
                public array \$items;
            }
        }
        PHP);

        try {
            require $file;

            foreach (['First' => $first, 'Second' => $second] as $name => $expected) {
                $this->assertSame(
                    $expected,
                    PropertyReflector::fromParts($namespace . '\\' . $name, 'items')
                        ->getIterableType()
                        ?->getName(),
                );
            }
        } finally {
            unlink($file);
        }
    }

    #[Test]
    public function iterable_type_resolves_imported_short_names(): void
    {
        $model = new ClassReflector(DocblockCollectionModel::class);

        $this->assertSame(DocblockImportTarget::class, $model->getProperty('imported')->getIterableType()->getName());
        $this->assertSame(DocblockImportTarget::class, $model->getProperty('aliased')->getIterableType()->getName());
    }

    #[Test]
    public function iterable_type_resolves_relative_and_fully_qualified_names(): void
    {
        $model = new ClassReflector(DocblockCollectionModel::class);

        $this->assertSame(DocblockRelativeTarget::class, $model->getProperty('relative')->getIterableType()->getName());
        $this->assertSame(DocblockImportTarget::class, $model->getProperty('fullyQualified')->getIterableType()->getName());
        $this->assertSame(DocblockImportTarget::class, $model->getProperty('list')->getIterableType()->getName());
        $this->assertSame(DocblockImportTarget::class, $model->getProperty('arrayType')->getIterableType()->getName());
    }

    #[Test]
    public function iterable_type_returns_null_without_a_docblock(): void
    {
        $model = new ClassReflector(DocblockCollectionModel::class);

        $this->assertNull($model->getProperty('noDocblock')->getIterableType());
    }

    #[Test]
    public function iterable_type_on_unreadable_file_falls_back_to_namespace_resolution(): void
    {
        $file = sys_get_temp_dir() . '/tempest-unreadable-probe-' . bin2hex(random_bytes(8)) . '.php';
        file_put_contents($file, <<<'PHP'
        <?php

        declare(strict_types=1);

        namespace TempestEvalProbe;

        final class DocblockHolder
        {
            /** @var Missing[] */
            public array $items;
        }
        PHP);

        require_once $file;
        unlink($file);

        $property = PropertyReflector::fromParts('TempestEvalProbe\\DocblockHolder', 'items');

        $this->assertSame('TempestEvalProbe\\Missing', $property->getIterableType()->getName());
    }
}
