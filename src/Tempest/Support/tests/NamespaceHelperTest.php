<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\NamespaceHelper;

/**
 * @internal
 */
final class NamespaceHelperTest extends TestCase
{
    #[DataProvider('namespaces')]
    #[Test]
    public function test_namespace_maker(array $namespaces, string $expected): void
    {
        $output = NamespaceHelper::make(...$namespaces);

        $this->assertSame($expected, $output);
    }

    public static function namespaces(): Generator
    {
        yield 'basic namespace' => [
            'namespaces' => ['Namespace'],
            'expected' => 'Namespace',
        ];

        yield 'namespace without PascalCase' => [
            'namespaces' => ['namespace'],
            'expected' => 'Namespace',
        ];

        yield 'namespace with subdirectory' => [
            'namespaces' => ['Subdirectory\\Namespace'],
            'expected' => 'Subdirectory\\Namespace',
        ];

        yield 'PascalCase multiple words' => [
            'namespaces' => ['subdirectory\\lowercase\\namespace'],
            'expected' => 'Subdirectory\\Lowercase\\Namespace',
        ];

        yield 'namespace with forward slash' => [
            'namespaces' => ['Namespace/With/ForwardSlash'],
            'expected' => 'Namespace\\With\\ForwardSlash',
        ];

        yield 'namespace with backward slash' => [
            'namespaces' => ['Namespace\\With\\BackwardSlash'],
            'expected' => 'Namespace\\With\\BackwardSlash',
        ];

        yield 'namespace with mixed slashes' => [
            'namespaces' => ['Namespace/With\\Mixed/Slashes'],
            'expected' => 'Namespace\\With\\Mixed\\Slashes',
        ];

        yield 'namespace end with forward slash' => [
            'namespaces' => ['Namespace\\With\\ForwardSlash/'],
            'expected' => 'Namespace\\With\\ForwardSlash',
        ];

        yield 'namespace end with backward slash' => [
            'namespaces' => ['Namespace\\With\\BackwardSlash\\'],
            'expected' => 'Namespace\\With\\BackwardSlash',
        ];

        yield 'namespace begin with forward slash' => [
            'namespaces' => ['/Namespace\\With\\ForwardSlash'],
            'expected' => 'Namespace\\With\\ForwardSlash',
        ];

        yield 'namespace begin with backward slash' => [
            'namespaces' => ['\\Namespace\\With\\BackwardSlash'],
            'expected' => 'Namespace\\With\\BackwardSlash',
        ];

        yield 'multiple namespaces' => [
            'namespaces' => ['Namespace', 'With', 'Multiple', 'Namespaces'],
            'expected' => 'Namespace\\With\\Multiple\\Namespaces',
        ];

        yield 'multiple namespaces with forwards slash at the end' => [
            'namespaces' => ['Namespace/', 'With/', 'Multiple/', 'Namespaces/'],
            'expected' => 'Namespace\\With\\Multiple\\Namespaces',
        ];

        yield 'multiple namespaces with backwards slash at the end' => [
            'namespaces' => ['Namespace\\', 'With\\', 'Multiple\\', 'Namespaces\\'],
            'expected' => 'Namespace\\With\\Multiple\\Namespaces',
        ];

        yield 'multiple namespaces with forwards slash at the beginning' => [
            'namespaces' => ['/Namespace', '/With', '/Multiple', '/Namespaces'],
            'expected' => 'Namespace\\With\\Multiple\\Namespaces',
        ];

        yield 'multiple namespaces with backwards slash at the beginning' => [
            'namespaces' => ['\\Namespace', '\\With', '\\Multiple', '\\Namespaces'],
            'expected' => 'Namespace\\With\\Multiple\\Namespaces',
        ];

        yield 'multiple namespaces with mixed slash at the beginning' => [
            'namespaces' => ['\\Namespace', '/With', '\\Multiple', '/Namespaces'],
            'expected' => 'Namespace\\With\\Multiple\\Namespaces',
        ];

        yield 'multiple namespaces with mixed slash at the end' => [
            'namespaces' => ['Namespace\\', 'With/', 'Multiple\\', 'Namespaces/'],
            'expected' => 'Namespace\\With\\Multiple\\Namespaces',
        ];

        yield 'multiple namespaces with mixed slash at the beginning and end' => [
            'namespaces' => ['/Namespace\\', '\\With/', 'Multiple\\', '\\Namespaces/'],
            'expected' => 'Namespace\\With\\Multiple\\Namespaces',
        ];
    }
}
