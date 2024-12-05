<?php

declare(strict_types=1);

namespace Tempest\Mailer\Tests\Components\Address;

use PHPUnit\Framework\TestCase;
use Tempest\Mailer\Components\Address\Address;
use Tempest\Mailer\Components\Address\AddressCollection;
use Tempest\Mailer\Components\Address\ImmutableAddressCollection;

/**
 * @internal
 */
class AddressCollectionTest extends TestCase
{
    public function test_adding_address_string(): void
    {
        $collection = new AddressCollection();

        $collection->add('jim.halpert@dundermifflinpaper.biz');

        $this->assertContainsEquals(
            new Address('jim.halpert@dundermifflinpaper.biz'),
            $collection->all(),
        );
    }

    public function test_adding_address_object(): void
    {
        $collection = new AddressCollection();

        $collection->add(
            new Address('jim.halpert@dundermifflinpaper.biz', 'Jim Halpert'),
        );

        $this->assertContainsEquals(
            new Address('jim.halpert@dundermifflinpaper.biz', 'Jim Halpert'),
            $collection->all(),
        );
    }

    public function test_removing_address_string(): void
    {
        $address = new Address('jim.halpert@dundermifflinpaper.biz');

        $collection = new AddressCollection([$address]);

        $collection->remove('jim.halpert@dundermifflinpaper.biz');

        $this->assertEmpty($collection->all());
    }

    public function test_removing_address_object(): void
    {
        $address = new Address('jim.halpert@dundermifflinpaper.biz');

        $collection = new AddressCollection([$address]);

        $collection->remove($address);

        $this->assertEmpty($collection->all());
    }

    public function test_to_immutable(): void
    {
        $collection = new AddressCollection([
            new Address('jim.halpert@dundermifflinpaper.biz'),
        ]);

        $this->assertInstanceOf(ImmutableAddressCollection::class, $collection->toImmutable());
    }
}
