<?php

namespace Tests\Tempest\Integration\Database;

use Tests\Tempest\Integration\Database\Fixtures\ModelWithSerializedDto;
use Tests\Tempest\Integration\Database\Fixtures\ModelWithSerializedDtoProperty;
use Tests\Tempest\Integration\Database\Fixtures\ModelWithVirtualDto;
use Tests\Tempest\Integration\Database\Fixtures\ModelWithVirtualHasMany;
use Tests\Tempest\Integration\IntegrationTestCase;

use function Tempest\Database\model;

final class ModelInspectorTest extends IntegrationTestCase
{
    public function test_virtual_array_is_never_a_relation(): void
    {
        $this->assertFalse(model(ModelWithVirtualHasMany::class)->isRelation('dtos'));
    }

    public function test_virtual_property_is_never_a_relation(): void
    {
        $this->assertFalse(model(ModelWithVirtualDto::class)->isRelation('dto'));
    }

    public function test_serialized_property_type_is_never_a_relation(): void
    {
        $this->assertFalse(model(ModelWithSerializedDto::class)->isRelation('dto'));
    }

    public function test_serialized_property_is_never_a_relation(): void
    {
        $this->assertFalse(model(ModelWithSerializedDtoProperty::class)->isRelation('dto'));
    }
}
