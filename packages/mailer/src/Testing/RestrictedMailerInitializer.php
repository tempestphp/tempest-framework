<?php

namespace Tempest\Mail\Testing;

use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Container\Singleton;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Mail\Mailer;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

#[SkipDiscovery]
final class RestrictedMailerInitializer implements DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return $class->getType()->matches(Mailer::class);
    }

    #[Singleton]
    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): Mailer
    {
        return new RestrictedMailer($tag);
    }
}
