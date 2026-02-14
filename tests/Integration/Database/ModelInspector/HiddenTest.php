<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tempest\Mapper\Hidden;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;
use function Tempest\Database\query;
use function Tempest\Mapper\map;

final class HiddenTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function hidden_property_is_excluded_from_select_fields(): void
    {
        $model = inspect(HiddenTestModel::class);
        $selectFields = $model->getSelectFields();

        $this->assertContains('id', $selectFields->toArray());
        $this->assertContains('name', $selectFields->toArray());
        $this->assertNotContains('password', $selectFields->toArray());
        $this->assertNotContains('secret', $selectFields->toArray());
    }

    #[Test]
    public function hidden_property_is_included_in_property_values(): void
    {
        $instance = new HiddenTestModel();
        $instance->id = new PrimaryKey(1);
        $instance->name = 'John';
        $instance->password = 'secret123'; // @mago-expect lint:no-literal-password
        $instance->secret = 'my-secret'; // @mago-expect lint:no-literal-password

        $model = inspect($instance);
        $propertyValues = $model->getPropertyValues();

        $this->assertArrayHasKey('name', $propertyValues);
        $this->assertArrayHasKey('password', $propertyValues);
        $this->assertArrayHasKey('secret', $propertyValues);
        $this->assertSame('John', $propertyValues['name']);
        $this->assertSame('secret123', $propertyValues['password']);
        $this->assertSame('my-secret', $propertyValues['secret']);
    }

    #[Test]
    public function hidden_property_is_excluded_from_serialization(): void
    {
        $object = new HiddenTestModel();
        $object->id = new PrimaryKey(1);
        $object->name = 'John';
        $object->password = 'secret123'; // @mago-expect lint:no-literal-password
        $object->secret = 'my-secret'; // @mago-expect lint:no-literal-password

        $array = map($object)->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('secret', $array);
    }

    #[Test]
    public function hidden_property_is_excluded_from_json_serialization(): void
    {
        $object = new HiddenTestModel();
        $object->id = new PrimaryKey(1);
        $object->name = 'John';
        $object->password = 'secret123'; // @mago-expect lint:no-literal-password
        $object->secret = 'my-secret'; // @mago-expect lint:no-literal-password

        $json = map($object)->toJson();

        $this->assertStringContainsString('"name":"John"', $json);
        $this->assertStringNotContainsString('password', $json);
        $this->assertStringNotContainsString('secret', $json);
    }

    #[Test]
    public function include_adds_hidden_fields_to_query(): void
    {
        $sql = HiddenTestModel::select()->compile()->toString();

        $this->assertStringNotContainsString('password', $sql);
        $this->assertStringNotContainsString('secret', $sql);

        $sql = HiddenTestModel::select()
            ->include('password')
            ->compile()
            ->toString();

        $this->assertStringContainsString('password', $sql);
        $this->assertStringNotContainsString('secret', $sql);

        $sql = HiddenTestModel::select()
            ->include('password', 'secret')
            ->compile()
            ->toString();

        $this->assertStringContainsString('password', $sql);
        $this->assertStringContainsString('secret', $sql);
    }

    #[Test]
    public function include_with_already_selected_field_is_ignored(): void
    {
        $sql = HiddenTestModel::select()
            ->include('name')
            ->compile()
            ->toString();

        $this->assertSame(2, substr_count($sql, 'name'));
    }

    #[Test]
    public function include_with_duplicate_selected_field_is_filtered(): void
    {
        $sql = HiddenTestModel::select()
            ->include('password', 'password')
            ->compile()
            ->toString();

        $this->assertSame(2, substr_count($sql, 'password'));
    }

    #[Test]
    public function select_with_duplicate_selected_field_is_filtered(): void
    {
        $sql = query(HiddenTestModel::class)->select('name', 'name')->include('name')->compile()->toString();

        $this->assertSame(1, substr_count($sql, 'name'));
    }
}

#[Table('hidden_test')]
final class HiddenTestModel
{
    use IsDatabaseModel;

    public PrimaryKey $id;

    public string $name;

    #[Hidden]
    public string $password;

    #[Hidden]
    public string $secret;
}
