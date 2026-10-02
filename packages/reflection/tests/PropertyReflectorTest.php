<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\Tests\Fixtures\DocblockCollectionModel;
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
}
