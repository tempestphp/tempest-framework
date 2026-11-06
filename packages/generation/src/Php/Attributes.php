<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * @internal
 */
final class Attributes
{
    /**
     * @param array<array-key, mixed> $arguments
     */
    public static function add(Class_|ClassMethod|Param $node, string $name, array $arguments = []): void
    {
        $node->attrGroups[] = new AttributeGroup([
            new Attribute(new FullyQualified(ltrim($name, '\\')), ValueExporter::exportArguments($arguments)),
        ]);
    }

    public static function remove(Class_|ClassMethod|Param $node, string $name): void
    {
        $groups = [];

        foreach ($node->attrGroups as $group) {
            $attributes = array_values(array_filter(
                $group->attrs,
                static fn (Attribute $attribute) => ! Names::refersTo($attribute->name, $name),
            ));

            if ($attributes === []) {
                continue;
            }

            if (count($attributes) !== count($group->attrs)) {
                $group->attrs = $attributes;
            }

            $groups[] = $group;
        }

        $node->attrGroups = $groups;
    }
}
