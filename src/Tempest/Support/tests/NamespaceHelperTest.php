<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\NamespaceHelper;

/**
 * @internal
 */
class NamespaceHelperTest extends TestCase
{
    #[DataProvider('namespaces')]
    #[Test]
    public function test_namespace_maker(string $namespaces, string $expected): void {
        $output = NamespaceHelper::make($namespaces);

        $this->assertSame($expected, $output);
    }

    public static function namespaces(): \Generator {
        yield 'basic namespace' => [
            'namespaces' => 'Namespace',
            'expected'   => 'Namespace',
        ];

        yield 'namespace without PascalCase' => [
            'namespaces' => 'namespace',
            'expected'   => 'Namespace',
        ];

        yield 'namespace with subdirectory' => [
            'namespaces' => 'Subdirectory\\Namespace',
            'expected'   => 'Subdirectory\\Namespace',
        ];

        yield 'PascalCase multiple words' => [
            'namespaces' => 'subdirectory\\lowercase\\namespace',
            'expected'   => 'Subdirectory\\Lowercase\\Namespace',
        ];

        yield 'namespace with forward slash' => [
            'namespaces' => 'Namespace/With/ForwardSlash',
            'expected'   => 'Namespace\\With\\ForwardSlash',
        ];

        yield 'namespace with backward slash' => [
            'namespaces' => 'Namespace\\With\\BackwardSlash',
            'expected'   => 'Namespace\\With\\BackwardSlash',
        ];

        yield 'namespace with mixed slashes' => [
            'namespaces' => 'Namespace/With\\Mixed/Slashes',
            'expected'   => 'Namespace\\With\\Mixed\\Slashes',
        ];

        yield 'namespace end with forward slash' => [
            'namespaces' => 'Namespace\\With\\ForwardSlash/',
            'expected'   => 'Namespace\\With\\ForwardSlash',
        ];

        yield 'namespace end with backward slash' => [
            'namespaces' => 'Namespace\\With\\BackwardSlash\\',
            'expected'   => 'Namespace\\With\\BackwardSlash',
        ];

        yield 'namespace begin with forward slash' => [
            'namespaces' => '/Namespace\\With\\ForwardSlash',
            'expected'   => 'Namespace\\With\\ForwardSlash',
        ];

        yield 'namespace begin with backward slash' => [
            'namespaces' => '\\Namespace\\With\\BackwardSlash',
            'expected'   => 'Namespace\\With\\BackwardSlash',
        ];
    }
}
