<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\DatabaseContext;
use Tempest\Mapper\CasterFactory;
use Tempest\Mapper\SerializerFactory;
use Tempest\Reflection\ClassReflector;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithContextDialect;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithDialectSensitiveProperty;

/**
 * @internal
 */
final class ContextualFactoryCacheTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function serializers_are_not_shared_between_context_payloads(): void
    {
        $property = new ClassReflector(ObjectWithDialectSensitiveProperty::class)->getProperty('published');
        $factory = $this->container->get(SerializerFactory::class);

        $mysql = $factory
            ->in(new DatabaseContext(DatabaseDialect::MYSQL))
            ->forProperty($property);

        $postgresql = $factory
            ->in(new DatabaseContext(DatabaseDialect::POSTGRESQL))
            ->forProperty($property);

        $this->assertSame('1', $mysql->serialize(true));
        $this->assertSame('true', $postgresql->serialize(true));
    }

    #[Test]
    public function casters_are_not_shared_between_context_payloads(): void
    {
        $property = new ClassReflector(ObjectWithContextDialect::class)->getProperty('dialect');
        $factory = $this->container->get(CasterFactory::class);

        $mysql = $factory
            ->in(new DatabaseContext(DatabaseDialect::MYSQL))
            ->forProperty($property);

        $postgresql = $factory
            ->in(new DatabaseContext(DatabaseDialect::POSTGRESQL))
            ->forProperty($property);

        $this->assertSame('MYSQL', $mysql->cast('input'));
        $this->assertSame('POSTGRESQL', $postgresql->cast('input'));
    }
}
