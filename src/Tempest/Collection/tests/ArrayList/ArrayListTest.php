<?php

declare(strict_types=1);

namespace Tempest\Collection\Tests\ArrayList;

use PHPUnit\Framework\TestCase;
use Tempest\Collection\ArrayList\ArrayList;

/**
 * @internal
 */
class ArrayListTest extends TestCase
{
    public function test_adding_value(): void
    {
        $list = new ArrayList();

        $list->add('T-Rex');

        $this->assertSame($list[0], 'T-Rex');
    }

    public function test_inserting_value(): void
    {
        $list = new ArrayList();

        $list->insert(10, 'Velociraptor');

        $this->assertSame($list[10], 'Velociraptor');
    }

    public function test_removing_value(): void
    {
        $list = new ArrayList();

        $list->add('T-Rex');
        $list->add('T-Rex');
        $list->add('T-Rex');

        $list->remove('T-Rex');

        $this->assertFalse(isset($list[0]));
        $this->assertSame($list[1], 'T-Rex');
        $this->assertSame($list[2], 'T-Rex');
    }

    public function test_removing_all_values(): void
    {
        $list = new ArrayList();

        $list->add('T-Rex');
        $list->add('T-Rex');
        $list->add('T-Rex');

        $list->removeAll('T-Rex');

        $this->assertFalse(isset($list[0]));
        $this->assertFalse(isset($list[1]));
        $this->assertFalse(isset($list[2]));
    }

    public function test_remove_at(): void
    {
        $list = new ArrayList();

        $list->add('T-Rex');
        $list->add('T-Rex');
        $list->add('T-Rex');

        $list->removeAt(1);

        $this->assertSame($list[0], 'T-Rex');
        $this->assertFalse(isset($list[1]));
        $this->assertSame($list[2], 'T-Rex');
    }

    public function test_clear(): void
    {
        $list = new ArrayList();

        $list->add('T-Rex');
        $list->add('Velociraptor');

        $list->clear();

        $this->assertFalse(isset($list[0]));
        $this->assertFalse(isset($list[1]));
    }
}
