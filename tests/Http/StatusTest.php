<?php

namespace Tests\Tempest\Http;

use PHPUnit\Framework\TestCase;
use Tempest\Http\Status;

class StatusTest extends TestCase
{
    public function test_status_code_factory()
    {
        $this->assertSame(Status::OK, Status::code(200));
        $this->assertSame(Status::IM_A_TEAPOT, Status::code(418));
    }
}