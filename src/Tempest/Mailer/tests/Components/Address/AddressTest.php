<?php

declare(strict_types=1);

namespace Tempest\Mailer\Tests\Components\Address;

use PHPUnit\Framework\TestCase;
use Tempest\Mailer\Components\Address\Address;

/**
 * @internal
 */
class AddressTest extends TestCase
{
    public function test_setting_and_getting_an_address(): void
    {
        $address = new Address('jim.halpert@dundermifflinpaper.biz', 'Jim Halpert');

        $this->assertSame('jim.halpert@dundermifflinpaper.biz', $address->address);
        $this->assertSame('Jim Halpert', $address->name);
    }
}
