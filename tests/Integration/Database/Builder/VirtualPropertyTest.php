<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Builder\QueryBuilders\InsertQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\UpdateQueryBuilder;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Virtual;
use Tempest\Mapper\SerializerFactory;
use Tests\Tempest\Integration\IntegrationTestCase;

final class VirtualPropertyTest extends IntegrationTestCase
{
    #[Test]
    public function virtual_properties_are_excluded_from_insert(): void
    {
        $model = new VirtualPropertyTestModel();
        $model->regularProperty = 'test_value';

        $builder = new InsertQueryBuilder(
            model: $model,
            rows: [$model],
            serializerFactory: $this->container->get(SerializerFactory::class),
        );

        $sql = $builder->compile()->toString();

        // The SQL should not contain the virtual property columns
        $this->assertStringNotContainsString('virtualWithAttribute', $sql);
        $this->assertStringNotContainsString('virtualWithGetHook', $sql);
        $this->assertStringNotContainsString('bindingValue', $sql);

        // But should contain the regular property
        $this->assertStringContainsString('regularProperty', $sql);
    }

    #[Test]
    public function virtual_properties_are_excluded_from_update(): void
    {
        $model = new VirtualPropertyTestModel();
        $model->id = new PrimaryKey(1);
        $model->regularProperty = 'updated_value';

        $builder = new UpdateQueryBuilder(
            model: $model,
            values: [
                'regularProperty' => 'updated_value',
            ],
            serializerFactory: $this->container->get(SerializerFactory::class),
        );

        $sql = $builder->compile()->toString();

        // The SQL should not contain the virtual property columns
        $this->assertStringNotContainsString('virtualWithAttribute', $sql);
        $this->assertStringNotContainsString('virtualWithGetHook', $sql);
        $this->assertStringNotContainsString('bindingValue', $sql);

        // But should contain the regular property
        $this->assertStringContainsString('regularProperty', $sql);
    }

    #[Test]
    public function virtual_properties_work_as_expected(): void
    {
        $model = new VirtualPropertyTestModel();
        $model->id = new PrimaryKey(5);
        $model->regularProperty = 'test';

        // Virtual properties should be accessible and computed correctly
        $this->assertSame('Virtual: test', $model->virtualWithAttribute);
        $this->assertSame(10, $model->virtualWithGetHook);
        $this->assertSame('5', $model->bindingValue);
    }
}

final class VirtualPropertyTestModel
{
    use IsDatabaseModel;

    public string $regularProperty;

    #[Virtual]
    public string $virtualWithAttribute {
        get => 'Virtual: ' . $this->regularProperty;
    }

    // This is a virtual property by PHP's definition (has a get hook)
    // Even without the #[Virtual] attribute, it should be excluded
    public int $virtualWithGetHook {
        get => $this->id->value * 2;
    }

    // Another example of a virtual property with get hook
    #[Virtual]
    public int|string $bindingValue {
        get => (string) $this->id->value;
    }
}
