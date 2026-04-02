<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

use Tempest\Generation\TypeScript\TypeNodes\ArrayTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\IntersectionTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\LiteralTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\ObjectTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\ObjectTypePropertyNode;
use Tempest\Generation\TypeScript\TypeNodes\PrimitiveTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\RawTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\SymbolTypeNode;
use Tempest\Generation\TypeScript\TypeNodes\TypeNode;
use Tempest\Generation\TypeScript\TypeNodes\UnionTypeNode;

/**
 * Renders semantic type nodes to TypeScript text.
 */
final readonly class TypeNodeRenderer
{
    /**
     * @param callable(string):string $symbolRenderer
     */
    public function render(TypeNode $type, callable $symbolRenderer): string
    {
        return match (true) {
            $type instanceof PrimitiveTypeNode => $type->name,
            $type instanceof RawTypeNode => $type->expression,
            $type instanceof LiteralTypeNode => $this->renderLiteral($type),
            $type instanceof SymbolTypeNode => $symbolRenderer($type->fqcn),
            $type instanceof ArrayTypeNode => $this->render($type->type, $symbolRenderer) . '[]',
            $type instanceof ObjectTypeNode => $this->renderObjectType($type, $symbolRenderer),
            $type instanceof UnionTypeNode => implode(' | ', array_map(
                callback: fn (TypeNode $part): string => $this->render($part, $symbolRenderer),
                array: $type->types,
            )),
            $type instanceof IntersectionTypeNode => implode(' & ', array_map(
                callback: fn (TypeNode $part): string => $this->render($part, $symbolRenderer),
                array: $type->types,
            )),
            default => 'any',
        };
    }

    private function renderLiteral(LiteralTypeNode $type): string
    {
        if (is_string($type->value)) {
            return sprintf("'%s'", addcslashes($type->value, "\\'\n\r\t\v\f"));
        }

        if (is_bool($type->value)) {
            return $type->value ? 'true' : 'false';
        }

        return (string) $type->value;
    }

    /**
     * @param callable(string):string $symbolRenderer
     */
    private function renderObjectType(ObjectTypeNode $type, callable $symbolRenderer): string
    {
        $properties = array_map(
            callback: fn (ObjectTypePropertyNode $property): string => vsprintf('%s%s: %s', [
                $this->renderObjectPropertyName($property->name),
                $property->optional ? '?' : '',
                $this->render($property->type, $symbolRenderer),
            ]),
            array: $type->properties,
        );

        return '{ ' . implode('; ', $properties) . '; }';
    }

    private function renderObjectPropertyName(string $name): string
    {
        if (preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $name) === 1) {
            return $name;
        }

        return sprintf("'%s'", addcslashes($name, "\\'\n\r\t\v\f"));
    }
}
