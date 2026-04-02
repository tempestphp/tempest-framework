<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\TypeScript\Writers;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Generation\TypeScript\InterfaceDefinition;
use Tempest\Generation\TypeScript\PropertyDefinition;
use Tempest\Generation\TypeScript\TypeDefinition;
use Tempest\Generation\TypeScript\TypeNodes\IntersectionTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\LiteralTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\PrimitiveTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\RawTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\SymbolTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\UnionTypeNode;
use Tempest\Generation\TypeScript\TypeScriptOutput;
use Tempest\Generation\TypeScript\Writers\NamespacedFileWriter;
use Tempest\Generation\TypeScript\Writers\NamespacedTypeScriptGenerationConfig;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Filesystem;

final class NamespacedFileWriterTest extends TestCase
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
    public function writes_types_file(): void
    {
        $outputPath = $this->directory . '/types.d.ts';
        $config = new NamespacedTypeScriptGenerationConfig(filename: $outputPath);
        $writer = new NamespacedFileWriter($config);

        $userInterface = new InterfaceDefinition(
            class: 'App\\Models\\User',
            originalType: new TypeReflector('string'),
            properties: [
                new PropertyDefinition(
                    name: 'id',
                    type: new PrimitiveTypeNode('number'),
                    isNullable: false,
                ),
                new PropertyDefinition(
                    name: 'username',
                    type: new PrimitiveTypeNode('string'),
                    isNullable: false,
                ),
                new PropertyDefinition(
                    name: 'email',
                    type: new PrimitiveTypeNode('string'),
                    isNullable: true,
                ),
            ],
        );

        $arrayType = new TypeDefinition(
            class: 'App\\Models\\Tags',
            originalType: new TypeReflector('array'),
            type: new RawTypeNode('Array<string>'),
            isNullable: false,
        );

        $unionType = new TypeDefinition(
            class: 'App\\Models\\Role',
            originalType: new TypeReflector('string'),
            type: new UnionTypeNode([
                new LiteralTypeNode('admin'),
                new LiteralTypeNode('user'),
                new LiteralTypeNode('guest'),
            ]),
            isNullable: false,
        );

        $intersectionType = new TypeDefinition(
            class: 'App\\Models\\AdminUser',
            originalType: new TypeReflector('object'),
            type: new IntersectionTypeNode([
                new SymbolTypeNode('App\\Models\\User'),
                new RawTypeNode('{ permissions: Array<string> }'),
            ]),
            isNullable: false,
        );

        $customBoolean = new TypeDefinition(
            class: 'App\\Scalars\\CustomBoolean',
            originalType: new TypeReflector('bool'),
            type: new PrimitiveTypeNode('boolean'),
            isNullable: false,
        );

        $output = new TypeScriptOutput(
            namespaces: [
                'App\\Models' => [
                    $userInterface,
                    $arrayType,
                    $unionType,
                    $intersectionType,
                ],
                'App\\Scalars' => [$customBoolean],
            ],
        );

        $writer->write($output);
        $content = Filesystem\read_file($outputPath);

        $this->assertStringContainsString('export namespace App.Models {', $content);
        $this->assertStringContainsString('export namespace App.Scalars {', $content);
        $this->assertStringContainsString('export interface User {', $content);
        $this->assertStringContainsString('id: number;', $content);
        $this->assertStringContainsString('username: string;', $content);
        $this->assertStringContainsString('email?: string;', $content);
        $this->assertStringContainsString('export type Tags = Array<string>;', $content);
        $this->assertStringContainsString("export type Role = 'admin' | 'user' | 'guest';", $content);
        $this->assertStringContainsString('export type AdminUser = User & { permissions: Array<string> };', $content);
        $this->assertStringContainsString('export type CustomBoolean = boolean;', $content);
    }
}
