<?php

namespace Tests\Tempest\Integration\Database\DtoSerialization;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Mapper\MapperConfig;
use Tempest\Mapper\SerializeAs;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class UnionDtoSerializationTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function can_serialize_union_object(): void
    {
        $config = $this->container->get(MapperConfig::class);
        $config->serializeAs(TestUnionA::class, 'test_union_a');
        $config->serializeAs(TestUnionB::class, 'test_union_b');
        $config->serializeAs(TestNestedObject::class, 'test_object');

        $this->database->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '001_union_serialization';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_wrapper_models')
                    ->primary()
                    ->json('property');
            }
        });

        query(TestWrapperModel::class)->create(property: new TestUnionA(
            content: 'abc',
            testObject: new TestNestedObject('def'),
        ));

        query(TestWrapperModel::class)->create(property: new TestUnionB(
            message: '123',
            testObject: new TestNestedObject('456'),
        ));

        $results = query(TestWrapperModel::class)->all();

        $this->assertCount(2, $results);
        $this->assertInstanceOf(TestUnionA::class, $results[0]->property);
        $this->assertSame('abc', $results[0]->property->content);
        $this->assertInstanceOf(TestNestedObject::class, $results[0]->property->testObject);
        $this->assertSame('def', $results[0]->property->testObject->content);

        $this->assertInstanceOf(TestUnionB::class, $results[1]->property);
        $this->assertSame('123', $results[1]->property->message);
        $this->assertInstanceOf(TestNestedObject::class, $results[1]->property->testObject);
        $this->assertSame('456', $results[1]->property->testObject->content);
    }
}

final class TestWrapperModel
{
    public function __construct(
        public TestUnionA|TestUnionB $property,
    ) {}
}

#[SerializeAs('test_object')]
final class TestNestedObject
{
    public function __construct(
        public string $content,
    ) {}
}

#[SerializeAs('test_union_a')]
final class TestUnionA
{
    public function __construct(
        public string $content,
        public TestNestedObject $testObject,
    ) {}
}

#[SerializeAs('test_union_b')]
final class TestUnionB
{
    public function __construct(
        public string $message,
        public TestNestedObject $testObject,
    ) {}
}
