<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node;
use PhpParser\Node\Stmt;

/**
 * Tells which nodes of loaded code are identical to the node they were cloned from,
 * so the printer can copy their original code instead of reconstructing it.
 *
 * @internal
 */
final class Changes
{
    private const string CHANGED_ATTRIBUTE = 'tempest.changed';

    /**
     * The node types that `PhpFile`, `PhpClass` and `PhpMethod` change in place. They are only ever nested in each other,
     * so any other node can only change when a name inside it is replaced, which marks it as changed.
     */
    private const array MUTABLE_NODES = [
        Stmt\Namespace_::class => true,
        Stmt\Use_::class => true,
        Stmt\GroupUse::class => true,
        Stmt\Class_::class => true,
        Stmt\ClassMethod::class => true,
        Stmt\Declare_::class => true,
        Node\DeclareItem::class => true,
        Node\AttributeGroup::class => true,
        Node\Param::class => true,
        Node\UseItem::class => true,
    ];

    /**
     * Marks a node as changed, returning whether it wasn't marked yet.
     */
    public static function mark(Node $node): bool
    {
        if ($node->getAttribute(self::CHANGED_ATTRIBUTE) === true) {
            return false;
        }

        $node->setAttribute(self::CHANGED_ATTRIBUTE, true);

        return true;
    }

    public static function isUnchanged(Node $node): bool
    {
        $original = $node->getAttribute('origNode');

        if (! $original instanceof Node || $node->getAttribute(self::CHANGED_ATTRIBUTE) === true) {
            return false;
        }

        if (! isset(self::MUTABLE_NODES[$node::class])) {
            return true;
        }

        if ($node->getComments() !== $original->getComments()) {
            return false;
        }

        return array_all($node->getSubNodeNames(), static fn (string $name) => self::isSame($node->{$name}, $original->{$name}));
    }

    private static function isSame(mixed $value, mixed $original): bool
    {
        if ($value instanceof Node) {
            return $value->getAttribute('origNode') === $original && self::isUnchanged($value);
        }

        if (! is_array($value)) {
            return $value === $original;
        }

        if (! is_array($original) || count($value) !== count($original)) {
            return false;
        }

        return array_all($value, static fn (mixed $item, int|string $key) => array_key_exists($key, $original) && self::isSame($item, $original[$key]));
    }
}
