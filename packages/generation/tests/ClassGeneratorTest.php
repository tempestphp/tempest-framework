<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Generation\ClassGenerator;
use Tempest\Generation\Tests\Fixtures\Database\FakeMigration;
use Tempest\Generation\Tests\Fixtures\Database\FakeQueryStatement;

/**
 * @internal
 */
final class ClassGeneratorTest extends TestCase
{
    #[Test]
    public function creates_class_from_scratch(): void
    {
        $class = new ClassGenerator('CreateUsersTable', namespace: 'App');

        $class->simplifyImplements(false);
        $class->addImplement(FakeMigration::class);
        $class->setFinal();
        $class->setReadOnly();

        $class->addMethod('up', body: <<<PHP
            return (new \Tempest\Generation\Tests\Fixtures\Database\FakeCreateTableStatement(\Tempest\Generation\Tests\Fixtures\Database\MigrationModel::table()))
                ->primary()
                ->text('name');
        PHP, returnType: FakeQueryStatement::class);

        $class->addMethod('getName', body: <<<PHP
            return '0000-00-00_create_users_table';
        PHP, returnType: 'string');

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function creates_methods_with_parameters(): void
    {
        $class = new ClassGenerator('UserService', namespace: 'App\\Services');

        $class->simplifyImplements(true);
        $class->setFinal();
        $class->setReadOnly();

        $class->addMethod('findById', body: <<<PHP
            //
        PHP, parameters: ['id' => 'int'], returnType: '?App\\Models\\User');

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function simplifies_implements(): void
    {
        $class = new ClassGenerator('CreateUsersTable', namespace: 'App');

        $class->simplifyImplements();
        $class->addImplement(FakeMigration::class);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function simplify_class_names_by_default(): void
    {
        $class = new ClassGenerator('CreateUsersTable', namespace: 'App');

        $class->addMethod('up', body: <<<PHP
            return (new \Tempest\Generation\Tests\Fixtures\Database\FakeCreateTableStatement(\Tempest\Generation\Tests\Fixtures\Database\MigrationModel::table()))
                ->primary()
                ->text('name');
        PHP, returnType: FakeQueryStatement::class);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function does_not_simplify_class_names(): void
    {
        $class = new ClassGenerator('CreateUsersTable', namespace: 'App');

        $class->simplifyClassNamesInMethodBodies(false);
        $class->addMethod('up', body: <<<PHP
            return (new \Tempest\Generation\Tests\Fixtures\Database\FakeCreateTableStatement(\Tempest\Generation\Tests\Fixtures\Database\MigrationModel::table()))
                ->primary()
                ->text('name');
        PHP, returnType: FakeQueryStatement::class);

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function sets_file_modifiers(): void
    {
        $class = new ClassGenerator('CreateUsersTable', namespace: 'App');
        $class->setStrictTypes();
        $class->setFileComment('This file has been generated.');

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function sets_class_modifiers(): void
    {
        $class = new ClassGenerator('CreateUsersTable', namespace: 'App');
        $class->setFinal();
        $class->setReadOnly();
        $class->setClassComment('This creates a users table.');

        $this->assertMatchesSnapshot($class->print());
    }

    #[Test]
    public function sets_long_namespace(): void
    {
        $class = new ClassGenerator('MyClass', namespace: 'App\\Foo\\Bar\\Baz\\Qux');
        $this->assertMatchesSnapshot($class->print());
    }
}
