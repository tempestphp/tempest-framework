<?php

namespace Tempest\Testing\Tests;

use Tempest\Testing\Test;
use Tempest\Testing\Testers\TestsEvents;

use function Tempest\Testing\test;

final class BeforeAndAfterTest
{
    use TestsEvents;

    #[Test]
    public function test_before(): void
    {
        $this->events->preventPropagation();

        test()->succeed();
    }
}
