<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\TypeScript;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Generation\Tests\TypeScript\Fixtures\EnumMetadataTypeResolver;
use Tempest\Generation\Tests\TypeScript\Fixtures\Metadata\RoleWithMetadata;
use Tempest\Generation\Tests\TypeScript\Fixtures\MultilineEnum;
use Tempest\Generation\Tests\TypeScript\Fixtures\QuotedEnum;
use Tempest\Generation\Tests\TypeScript\Fixtures\UnionHolder;
use Tempest\Generation\Tests\TypeScript\Fixtures\User;
use Tempest\Generation\TypeScript\GenericTypeScriptGenerator;
use Tempest\Generation\TypeScript\StructureResolvers\ClassStructureResolver;
use Tempest\Generation\TypeScript\StructureResolvers\EnumStructureResolver;
use Tempest\Generation\TypeScript\TypeResolvers\ClassReferenceTypeResolver;
use Tempest\Generation\TypeScript\TypeResolvers\DateTimeTypeResolver;
use Tempest\Generation\TypeScript\TypeResolvers\EnumCaseTypeResolver;
use Tempest\Generation\TypeScript\TypeResolvers\EnumReferenceTypeResolver;
use Tempest\Generation\TypeScript\TypeResolvers\MixedTypeResolver;
use Tempest\Generation\TypeScript\TypeResolvers\ScalarTypeResolver;
use Tempest\Generation\TypeScript\Writers\NamespacedFileWriter;
use Tempest\Generation\TypeScript\Writers\NamespacedTypeScriptGenerationConfig;
use Tempest\Support\Filesystem;

final class TypeScriptGenerationTest extends TestCase
{
    private string $directory;

    #[PreCondition]
    protected function configure(): void
    {
        $this->directory = sys_get_temp_dir() . '/tempest_typescript_integration_' . uniqid();

        Filesystem\ensure_directory_exists($this->directory);
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        Filesystem\delete($this->directory);
    }

    #[Test]
    public function generates_types(): void
    {
        $path = $this->directory . '/types.d.ts';

        $container = new GenericContainer();
        $config = new NamespacedTypeScriptGenerationConfig(filename: $path);
        $config->resolvers = [
            ScalarTypeResolver::class,
            DateTimeTypeResolver::class,
            EnumCaseTypeResolver::class,
            EnumReferenceTypeResolver::class,
            ClassReferenceTypeResolver::class,
            MixedTypeResolver::class,
        ];
        $config->sources = [
            User::class,
            QuotedEnum::class,
            MultilineEnum::class,
            UnionHolder::class,
        ];

        $generator = new GenericTypeScriptGenerator(
            config: $config,
            classResolver: new ClassStructureResolver($config, $container),
            enumResolver: new EnumStructureResolver($config, $container),
        );

        new NamespacedFileWriter($config)->write($generator->generate());

        $content = Filesystem\read_file($path);

        $this->assertStringContainsString('export namespace Tempest.Generation.Tests.TypeScript.Fixtures {', $content);
        $this->assertStringContainsString('export interface User {', $content);
        $this->assertStringContainsString('full_name: string;', $content);
        $this->assertStringContainsString('email: string;', $content);
        $this->assertStringContainsString('age: number;', $content);
        $this->assertStringContainsString('created_at: string;', $content);
        $this->assertStringContainsString('roles: Security.Role[];', $content);
        $this->assertStringContainsString('settings: Settings;', $content);
        $this->assertStringContainsString('export interface Settings {', $content);
        $this->assertStringContainsString('theme: Theme;', $content);
        $this->assertStringContainsString('sidebar_open: boolean;', $content);
        $this->assertStringContainsString("export type Theme = 'dark' | 'light';", $content);
        $this->assertStringContainsString("export type QuotedEnum = 'O\\'Reilly';", $content);
        $this->assertStringContainsString("export type MultilineEnum = 'first\\nsecond';", $content);
        $this->assertStringContainsString('export interface UnionHolder {', $content);
        $this->assertStringContainsString('value: Security.Role | Security.Permission;', $content);
        $this->assertStringContainsString('export namespace Tempest.Generation.Tests.TypeScript.Fixtures.Security {', $content);
        $this->assertStringContainsString('export interface Role {', $content);
        $this->assertStringContainsString('name: string;', $content);
        $this->assertStringContainsString('permissions: Permission[];', $content);
    }

    #[Test]
    public function generates_enum_metadata_with_custom_resolver(): void
    {
        $path = $this->directory . '/types-metadata.d.ts';

        $container = new GenericContainer();
        $config = new NamespacedTypeScriptGenerationConfig(filename: $path);
        $config->sources = [RoleWithMetadata::class];
        $config->resolvers = [
            EnumMetadataTypeResolver::class,
            ScalarTypeResolver::class,
            DateTimeTypeResolver::class,
            EnumCaseTypeResolver::class,
            EnumReferenceTypeResolver::class,
            ClassReferenceTypeResolver::class,
            MixedTypeResolver::class,
        ];

        $generator = new GenericTypeScriptGenerator(
            config: $config,
            classResolver: new ClassStructureResolver($config, $container),
            enumResolver: new EnumStructureResolver($config, $container),
        );

        new NamespacedFileWriter($config)->write($generator->generate());

        $content = Filesystem\read_file($path);

        $this->assertStringContainsString(
            "export type RoleWithMetadata = { value: 'admin'; description: 'An administrator'; } | { value: 'user'; description: 'A normal user'; } | { value: 'guest'; description: 'A guest'; };",
            $content,
        );
    }
}
