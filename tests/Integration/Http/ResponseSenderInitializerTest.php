<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Http\GenericResponseSender;
use Tempest\Http\ResponseSenderInitializer;
use Tests\Tempest\Integration\TestCase;

class ResponseSenderInitializerTest extends TestCase
{
    public function test_response_sender_initializer()
    {
        $initializer = new ResponseSenderInitializer();

        $this->assertInstanceOf(
            GenericResponseSender::class,
            $initializer->initialize($this->container)
        );
    }
}
