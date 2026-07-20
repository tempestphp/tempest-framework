<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Router\GenericResponseSender;
use Tempest\Router\ResponseSenderInitializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ResponseSenderInitializerTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function response_sender_initializer(): void
    {
        $initializer = new ResponseSenderInitializer();

        $this->assertInstanceOf(
            GenericResponseSender::class,
            $initializer->initialize($this->container),
        );
    }
}
