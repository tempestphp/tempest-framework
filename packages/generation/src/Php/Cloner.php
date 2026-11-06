<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

/**
 * Clones parsed code so it can be changed and printed format-preserving, resolving names
 * and collecting the imports they rely on along the way.
 *
 * @internal
 */
final class Cloner
{
    private const int UNRESOLVED = 0;

    /** @var array<string, true> keys of the imports that names rely on */
    public array $importKeys = [];

    private ?string $namespace = null;

    /** @var array<int, array<string, string>> imported names by type and alias */
    private array $imports = [];

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function cloneStatements(array $stmts): array
    {
        /** @var Stmt[] */
        return $this->cloneValue($stmts, Stmt\Use_::TYPE_NORMAL);
    }

    private function cloneValue(mixed $value, int $nameType): mixed
    {
        if ($value instanceof Node) {
            return $this->cloneNode($value, $nameType);
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->cloneValue($item, $nameType);
        }

        return $value;
    }

    private function cloneNode(Node $node, int $nameType): Node
    {
        $clone = clone $node;
        $clone->setAttribute('origNode', $node);

        if ($clone instanceof Name) {
            $this->resolve($clone, $nameType);

            return $clone;
        }

        if ($node instanceof Stmt\Namespace_) {
            $this->namespace = $node->name?->toString();
            $this->imports = [];
        } elseif ($node instanceof Stmt\Use_ || $node instanceof Stmt\GroupUse) {
            $this->import($node);
        }

        $nameType = $this->nameTypeOf($node);

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $clone->{$subNodeName} = $this->cloneValue($node->{$subNodeName}, $nameType);
        }

        return $clone;
    }

    /**
     * Returns the type of the names that are direct children of a node.
     */
    private function nameTypeOf(Node $node): int
    {
        return match (true) {
            $node instanceof Stmt\Namespace_, $node instanceof Stmt\Use_, $node instanceof Stmt\GroupUse, $node instanceof Node\UseItem => self::UNRESOLVED,
            $node instanceof Expr\FuncCall => Stmt\Use_::TYPE_FUNCTION,
            $node instanceof Expr\ConstFetch => Stmt\Use_::TYPE_CONSTANT,
            default => Stmt\Use_::TYPE_NORMAL,
        };
    }

    private function import(Stmt\Use_|Stmt\GroupUse $node): void
    {
        foreach ($node->uses as $item) {
            $type = $node->type !== Stmt\Use_::TYPE_UNKNOWN ? $node->type : $item->type;
            $name = $node instanceof Stmt\GroupUse ? Name::concat($node->prefix, $item->name) : $item->name;

            $this->imports[$type][self::aliasKey($type, $item->getAlias()->toString())] = $name->toString();
        }
    }

    private function resolve(Name $name, int $type): void
    {
        if ($type === self::UNRESOLVED) {
            return;
        }

        $importKey = Imports::keyOf($name, $type);

        if ($importKey !== null) {
            $this->importKeys[$importKey] = true;
        }

        if ($name->isFullyQualified() || $name->isSpecialClassName()) {
            return;
        }

        if ($name->isRelative()) {
            $resolved = $this->prefixNamespace($name->toString());
        } elseif ($type !== Stmt\Use_::TYPE_NORMAL && $name->isUnqualified()) {
            $resolved = $this->imports[$type][self::aliasKey($type, $name->toString())] ?? $this->resolveGlobally($name, $type);
        } else {
            $import = $this->imports[Stmt\Use_::TYPE_NORMAL][strtolower($name->getFirst())] ?? null;
            $resolved = match (true) {
                $import === null => $this->prefixNamespace($name->toString()),
                $name->isQualified() => $import . '\\' . $name->slice(1)?->toString(),
                default => $import,
            };
        }

        if ($resolved !== null) {
            $name->setAttribute(Names::RESOLVED_NAME_ATTRIBUTE, new Name\FullyQualified($resolved));
        }
    }

    /**
     * Unqualified functions and constants only refer to a known definition outside of a namespace,
     * since PHP falls back to the global one inside a namespace.
     */
    private function resolveGlobally(Name $name, int $type): ?string
    {
        if ($this->namespace !== null) {
            return null;
        }

        if ($type === Stmt\Use_::TYPE_CONSTANT && in_array($name->toLowerString(), ['true', 'false', 'null'], strict: true)) {
            return null;
        }

        return $name->toString();
    }

    private function prefixNamespace(string $name): string
    {
        return $this->namespace === null ? $name : $this->namespace . '\\' . $name;
    }

    private static function aliasKey(int $type, string $alias): string
    {
        return $type === Stmt\Use_::TYPE_CONSTANT ? $alias : strtolower($alias);
    }
}
