<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\TypeScript\StructureResolvers;

use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Generation\TypeScript\GenericTypeScriptGenerator;
use Tempest\Generation\TypeScript\InterfaceDefinition;
use Tempest\Generation\TypeScript\StructureResolvers\ClassStructureResolver;
use Tempest\Generation\TypeScript\StructureResolvers\EnumStructureResolver;
use Tempest\Generation\TypeScript\TypeResolvers\ClassReferenceTypeResolver;
use Tempest\Generation\TypeScript\TypeResolvers\DateTimeTypeResolver;
use Tempest\Generation\TypeScript\TypeResolvers\EnumReferenceTypeResolver;
use Tempest\Generation\TypeScript\TypeResolvers\MixedTypeResolver;
use Tempest\Generation\TypeScript\TypeResolvers\ScalarTypeResolver;
use Tempest\Generation\TypeScript\Writers\NamespacedTypeScriptGenerationConfig;
use Tempest\Reflection\TypeReflector;

final class ClassStructureResolverTest extends TestCase
{
    private ClassStructureResolver $resolver;
    private GenericTypeScriptGenerator $generator;

    #[PreCondition]
    protected function configure(): void
    {
        $container = new GenericContainer();
        $config = new NamespacedTypeScriptGenerationConfig(filename: 'test.d.ts');
        $config->resolvers = [
            ScalarTypeResolver::class,
            DateTimeTypeResolver::class,
            EnumReferenceTypeResolver::class,
            ClassReferenceTypeResolver::class,
            MixedTypeResolver::class,
        ];

        $this->resolver = new ClassStructureResolver($config, $container);
        $this->generator = new GenericTypeScriptGenerator(
            config: $config,
            classResolver: $this->resolver,
            enumResolver: new EnumStructureResolver($config, $container),
        );
    }

    #[Test]
    public function resolves_class_to_interface_definition(): void
    {
        $type = new TypeReflector(Badge::class);

        $result = $this->resolver->resolve($type, $this->generator);

        $this->assertInstanceOf(InterfaceDefinition::class, $result);
        $this->assertSame(Badge::class, $result->class);
        $this->assertCount(2, $result->properties);
    }

    #[Test]
    public function resolves_scalar_properties(): void
    {
        $type = new TypeReflector(Badge::class);

        $result = $this->resolver->resolve($type, $this->generator);

        $this->assertSame('name', $result->properties[0]->name);
        $this->assertSame('string', $result->properties[0]->definition);
        $this->assertFalse($result->properties[0]->isNullable);

        $this->assertSame('value', $result->properties[1]->name);
        $this->assertSame('number', $result->properties[1]->definition);
        $this->assertFalse($result->properties[1]->isNullable);
    }
}

final class Badge
{
    public string $name;
    public int $value;
}
