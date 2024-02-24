<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class ResponseSenderInitializer implements Initializer
{
    public function initialize(string $className, Container $container): object
    {
        $responseSender = new GenericResponseSender();

        $container->singleton(ResponseSender::class, fn () => $responseSender);

        return $responseSender;
    }
}
