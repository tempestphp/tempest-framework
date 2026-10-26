<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Idempotency\Fingerprint\RequestFingerprintGenerator;
use Tempest\Idempotency\HttpFingerprintGenerator;

final readonly class IdempotencyFingerprintGeneratorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): HttpFingerprintGenerator
    {
        return $container->get(RequestFingerprintGenerator::class);
    }
}
