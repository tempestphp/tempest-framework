<?php

declare(strict_types=1);

namespace Tempest\Mcp\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tempest\Mcp\Exceptions\ParameterWasNotSupported;
use Tempest\Mcp\SchemaGenerator;
use Tempest\Mcp\Tests\Fixtures\SchemaFixture;
use Tempest\Reflection\MethodReflector;

/**
 * @internal
 */
final class SchemaGeneratorTest extends TestCase
{
    #[Test]
    #[DataProvider('schemas')]
    public function derives_schemas_from_method_signatures(string $method, array $expected): void
    {
        $schema = new SchemaGenerator()->createInputSchema(MethodReflector::fromParts(SchemaFixture::class, $method));

        $this->assertEquals($expected, $schema);
    }

    public static function schemas(): iterable
    {
        yield 'scalars' => [
            'scalars',
            [
                'type' => 'object',
                'properties' => [
                    'a' => ['type' => 'string'],
                    'b' => ['type' => 'integer'],
                    'c' => ['type' => 'number'],
                    'd' => ['type' => 'boolean'],
                    'e' => ['type' => 'array'],
                ],
                'required' => ['a', 'b', 'c', 'd', 'e'],
            ],
        ];

        yield 'optionals' => [
            'optionals',
            [
                'type' => 'object',
                'properties' => [
                    'filter' => ['type' => ['string', 'null']],
                    'limit' => ['type' => 'integer', 'default' => 10],
                    'sort' => ['type' => ['string', 'null'], 'default' => null],
                ],
            ],
        ];

        yield 'enums' => [
            'enums',
            [
                'type' => 'object',
                'properties' => [
                    'suit' => ['type' => 'string', 'enum' => ['hearts', 'spades']],
                    'fallback' => ['type' => ['string', 'null'], 'enum' => ['hearts', 'spades', null], 'default' => null],
                ],
                'required' => ['suit'],
            ],
        ];

        yield 'injected services are excluded' => [
            'injected',
            [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string'],
                ],
                'required' => ['name'],
            ],
        ];

        yield 'validation attributes become constraints' => [
            'constrained',
            [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'minLength' => 2, 'maxLength' => 10],
                    'score' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                    'letter' => ['type' => 'string', 'enum' => ['a', 'b']],
                    'described' => ['type' => 'string', 'description' => 'The described parameter'],
                ],
                'required' => ['name', 'score', 'letter', 'described'],
            ],
        ];

        yield 'no schema parameters' => [
            'nothing',
            [
                'type' => 'object',
                'properties' => new stdClass(),
            ],
        ];
    }

    #[Test]
    public function derives_prompt_arguments(): void
    {
        $arguments = new SchemaGenerator()->createPromptArguments(MethodReflector::fromParts(SchemaFixture::class, 'optionals'));

        $this->assertSame(
            [
                ['name' => 'filter', 'required' => false],
                ['name' => 'limit', 'required' => false],
                ['name' => 'sort', 'required' => false],
            ],
            $arguments,
        );
    }

    #[Test]
    public function prompt_arguments_include_descriptions(): void
    {
        $arguments = new SchemaGenerator()->createPromptArguments(MethodReflector::fromParts(SchemaFixture::class, 'constrained'));

        $this->assertSame(['name' => 'described', 'description' => 'The described parameter', 'required' => true], $arguments[3]);
    }

    #[Test]
    public function variadic_parameters_are_not_supported(): void
    {
        $this->expectException(ParameterWasNotSupported::class);
        $this->expectExceptionMessage('is variadic');

        new SchemaGenerator()->assertSupported(MethodReflector::fromParts(SchemaFixture::class, 'variadic'));
    }

    #[Test]
    public function union_parameters_are_not_supported(): void
    {
        $this->expectException(ParameterWasNotSupported::class);
        $this->expectExceptionMessage('union type');

        new SchemaGenerator()->assertSupported(MethodReflector::fromParts(SchemaFixture::class, 'union'));
    }

    #[Test]
    public function supported_signatures_pass_the_guard(): void
    {
        $this->expectNotToPerformAssertions();

        foreach (['scalars', 'optionals', 'enums', 'injected', 'constrained', 'nothing'] as $method) {
            new SchemaGenerator()->assertSupported(MethodReflector::fromParts(SchemaFixture::class, $method));
        }
    }
}
