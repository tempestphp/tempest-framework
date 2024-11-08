<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Input;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tempest\Console\Input\ConsoleArgumentDefinition;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\ParameterReflector;
use Tests\Tempest\Fixtures\Console\CommandWithDifferentArguments;

/**
 * @internal
 */
final class ConsoleArgumentDefinitionTest extends TestCase
{
    #[TestWith(['string', 'string', 'string', null])]
    #[TestWith(['bool', 'bool', 'bool', null])]
    #[TestWith(['camelCaseString', 'camel-case-string', 'string', null])]
    #[TestWith(['camelCaseBool', 'camel-case-bool', 'bool', null])]
    #[TestWith(['renamedCamelString', 'my-camel-string', 'string', null])]
    #[TestWith(['renamedKebabString', 'my-kebab-string', 'string', null])]
    #[TestWith(['camelCaseBool', 'camel-case-bool', 'bool', null])]
    #[TestWith(['camelCaseStringWithDefault', 'camel-case-string-with-default', 'string', 'foo'])]
    #[TestWith(['camelCaseBoolWithTrueDefault', 'camel-case-bool-with-true-default', 'bool', true])]
    #[TestWith(['camelCaseBoolWithFalseDefault', 'camel-case-bool-with-false-default', 'bool', false])]
    public function test_parse_named_arguments_with_types_and_defaults(string $originalParameter, string $expectedName, string $expectedType, mixed $expectedDefault): void
    {
        $definition = ConsoleArgumentDefinition::fromParameter($this->getParameter($originalParameter));
        $this->assertSame($expectedName, $definition->name);
        $this->assertSame($expectedType, $definition->type);
        $this->assertSame($expectedDefault, $definition->default);
    }

    private function getParameter(string $name): ParameterReflector
    {
        $reflector = new ClassReflector(CommandWithDifferentArguments::class);

        foreach ($reflector->getMethod('__invoke')->getParameters() as $parameter) {
            if ($parameter->getName() === $name) {
                return $parameter;
            }
        }

        throw new RuntimeException("Parameter not found: $name");
    }
}
