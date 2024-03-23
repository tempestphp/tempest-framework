<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Collection\Generic;

use PHPUnit\Framework\TestCase;
use Tempest\Collection\Generic\GenericCollection;

/**
 * @internal
 * @small
 */
class GenericCollectionTest extends TestCase
{
    public function test_getting_items_from_a_collection(): void
    {
        $collection = new GenericCollection([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertSame('value1', $collection->get('key1'));
        $this->assertSame('value2', $collection->get('key2'));
    }

    public function test_determining_if_collection_has_key(): void
    {
        $collection = new GenericCollection([
            'key1' => 'value1',
        ]);

        $this->assertTrue($collection->has('key1'));
        $this->assertFalse($collection->has('key2'));
    }

    public function test_adding_items_to_a_collection(): void
    {
        $collection = new GenericCollection();

        $collection->add('testing');

        $this->assertContainsEquals('testing', $collection);
    }

    public function test_setting_keys_in_a_collection(): void
    {
        $collection = new GenericCollection(['key2' => 'value3']);

        $collection->set('key1', 'value1');
        $collection->set('key2', 'value2');

        $this->assertEqualsCanonicalizing([
            'key1' => 'value1',
            'key2' => 'value2',
        ], iterator_to_array($collection));
    }

    public function test_removing_item_from_collection_by_key(): void
    {
        $collection = new GenericCollection([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $collection->removeAt('key2');

        $this->assertFalse(
            $collection->has('key2')
        );
    }

    public function test_removing_item_from_collection_by_value(): void
    {
        $collection = new GenericCollection([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $collection->remove('value1');

        $this->assertFalse(
            $collection->has('key1')
        );
    }

    public function test_determining_index_of_item_in_collection(): void
    {
        $collection = new GenericCollection([
            'key1' => 'value1',
        ]);

        $this->assertSame(
            'key1',
            $collection->indexOf('value1')
        );
    }

    public function test_array_access(): void
    {
        $collection = new GenericCollection();

        $collection['key1'] = 'value1';

        $this->assertSame('value1', $collection['key1']);
        $this->assertTrue(isset($collection['key1']));

        unset($collection['key1']);

        $this->assertFalse(isset($collection['key1']));
    }
}
