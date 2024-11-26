<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Router\GenericResponseSender;
use Tempest\Router\ResponseSenderInitializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ResponseSenderInitializerTest extends FrameworkIntegrationTestCase
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
