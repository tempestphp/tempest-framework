<?php

declare(strict_types=1);

namespace Tempest\Testing\Http;

use Tempest\Container\CanInitialize;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Http\Response;
use Tempest\Http\ResponseSender;

final class TestResponseSenderInitializer implements Initializer, CanInitialize
{
    public function canInitialize(string $className): bool
    {
        return in_array($className, [Response::class, TestResponse::class]);
    }

    public function initialize(Container $container): object
    {
        $responseSender = new TestResponseSender();

        $container->singleton(ResponseSender::class, fn () => $responseSender);

        return $responseSender;
    }
}
