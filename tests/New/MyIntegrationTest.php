<?php

namespace Tests\Tempest\New;

use Tempest\Database\Database;
use Tempest\Testing\Test;
use function Tempest\Testing\test;

final class MyIntegrationTest
{
    #[Test]
    public function t(Database $database): void
    {
        test()->fail();
    }
}