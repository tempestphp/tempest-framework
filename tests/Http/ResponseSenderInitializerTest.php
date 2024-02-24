<?php

declare(strict_types=1);

namespace Tests\Tempest\Http;

use Tempest\Http\GenericResponseSender;
use Tempest\Http\ResponseSender;
use Tempest\Http\ResponseSenderInitializer;
use Tests\Tempest\TestCase;

class ResponseSenderInitializerTest extends TestCase
{
    public function test_response_sender_initializer()
    {
        $initializer = new ResponseSenderInitializer();

        $this->assertInstanceOf(
            GenericResponseSender::class,
            $initializer->initialize(ResponseSender::class, $this->container)
        );
    }
}
