<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Generation\ClassManipulator;
use Tempest\Generation\Tests\Fixtures\CreateMigrationsTable;
use Tempest\Generation\Tests\Fixtures\Database\MigrationModel;
use Tempest\Generation\Tests\Fixtures\TestAttribute;
use Tempest\Generation\Tests\Fixtures\WelcomeController;

/**
 * @internal
 */
final class ClassManipulatorTest extends TestCase
{
    #[Test]
    public function updates_namespace(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->setNamespace('App');

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function updates_namespace_multiple_times(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->setNamespace('App');
        $class->setNamespace('Database');

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
    public function does_not_simplify_implements_when_specified(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->simplifyImplements(false);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function set_aliases(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->setAlias(MigrationModel::class, 'Model');

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function simplifies_class_names_by_default(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function does_not_simplify_class_names_by_default(): void
    {
        $class = new ClassManipulator(CreateMigrationsTable::class);
        $class->simplifyClassNamesInMethodBodies(false);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function transforms_functions(): void
    {
        $class = new ClassManipulator(WelcomeController::class);
        $class->setNamespace('App\\Controllers');
        $class->setClassName('WelcomeController');

        $this->assertMatchesSnapshot($class->print());
    }
}
