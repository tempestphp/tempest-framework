<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Initializers;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Idempotency\Fingerprint\CommandFingerprintGenerator;
use Tempest\Idempotency\Fingerprint\ObjectFingerprintGenerator;

final readonly class CommandFingerprintGeneratorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): CommandFingerprintGenerator
    {
        return $container->get(ObjectFingerprintGenerator::class);
    }
}
