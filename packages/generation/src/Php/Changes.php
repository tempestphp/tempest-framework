<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\DeclareItem;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Param;
use PhpParser\Node\UseItem;
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
        Namespace_::class => true,
        Use_::class => true,
        GroupUse::class => true,
        Class_::class => true,
        ClassMethod::class => true,
        Declare_::class => true,
        DeclareItem::class => true,
        AttributeGroup::class => true,
        Param::class => true,
        UseItem::class => true,
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
