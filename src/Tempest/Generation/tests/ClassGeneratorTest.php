<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Generation\ClassManipulator;
use Tempest\Generation\Tests\Fixtures\CreateMigrationsTable;
use Tempest\Generation\Tests\Fixtures\TestAttribute;
use Tests\Tempest\TestCase;

/**
 * @internal
 */
final class ClassGeneratorTest extends TestCase
{
    #[Test]
    public function updates_namespace(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->updateNamespace('App');

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function updates_namespace_multiple_times(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->updateNamespace('App');
        $class->updateNamespace('Database');

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function removes_class_attributes(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->removeClassAttribute(TestAttribute::class);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function sets_class_final(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->setFinal(true);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function unsets_class_final(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->setFinal(false);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function sets_class_readonly(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->setReadOnly(true);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function unsets_class_readonly(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->setReadOnly(false);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function sets_strict_types(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->setStrictTypes(true);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function unsets_strict_types(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->setStrictTypes(false);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function simplifies_implements(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->simplifyImplements();

        $this->assertMatchesSnapshot($class->print());
    }

}
