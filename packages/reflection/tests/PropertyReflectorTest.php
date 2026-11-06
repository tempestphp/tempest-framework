<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\Tests\Fixtures\DocblockClosureUseModel;
use Tempest\Reflection\Tests\Fixtures\DocblockCollectionModel;
use Tempest\Reflection\Tests\Fixtures\DocblockMixedGroupModel;
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
    public function iterable_type_resolves_imported_short_names(): void
    {
        $model = new ClassReflector(DocblockCollectionModel::class);

        $this->assertSame(DocblockImportTarget::class, $model->getProperty('imported')->getIterableType()->getName());
        $this->assertSame(DocblockImportTarget::class, $model->getProperty('aliased')->getIterableType()->getName());
        $this->assertSame(DocblockGroupTargetA::class, $model->getProperty('group')->getIterableType()->getName());
        $this->assertSame(DocblockGroupTargetB::class, $model->getProperty('groupAliased')->getIterableType()->getName());
        $this->assertSame(DocblockGroupTargetB::class, $model->getProperty('groupName')->getIterableType()->getName());
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
    public function iterable_type_ignores_mixed_group_import_members(): void
    {
        $model = new ClassReflector(DocblockMixedGroupModel::class);

        $this->assertSame(DocblockGroupTargetA::class, $model->getProperty('items')->getIterableType()->getName());
    }

    #[Test]
    public function iterable_type_ignores_top_level_closure_use(): void
    {
        $model = new ClassReflector(DocblockClosureUseModel::class);

        $this->assertSame(DocblockImportTarget::class, $model->getProperty('items')->getIterableType()->getName());
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

        $property = new PropertyReflector(new \ReflectionProperty('TempestEvalProbe\\DocblockHolder', 'items')); // @phpstan-ignore argument.type (class exists only at runtime, after require)

        $this->assertSame('TempestEvalProbe\\Missing', $property->getIterableType()->getName());
    }
}
