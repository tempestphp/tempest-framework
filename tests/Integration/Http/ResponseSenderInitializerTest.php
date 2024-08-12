<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Http\GenericResponseSender;
use Tempest\Http\ResponseSenderInitializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class ResponseSenderInitializerTest extends FrameworkIntegrationTestCase
{
    public function test_response_sender_initializer(): void
    {
        $initializer = new ResponseSenderInitializer();

        $this->assertInstanceOf(
            GenericResponseSender::class,
            $initializer->initialize($this->container)
        );
    }
}
