<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\UseItem;

/**
 * The `use` statements of a namespace.
 *
 * @internal
 */
final class Imports
{
    /** @var array<string, string> import key to fully qualified name */
    private array $names = [];

    /** @var array<string, string> lowercased type and fully qualified name to alias */
    private array $aliases = [];

    /** @var array<string, array{Stmt\Use_::TYPE_*, string, ?string}> */
    private array $pending = [];

    /**
     * @param Stmt[] $stmts
     */
    public static function from(array $stmts): self
    {
        $imports = new self();

        foreach ($stmts as $stmt) {
            if ($stmt instanceof Stmt\Use_) {
                foreach ($stmt->uses as $item) {
                    $imports->register($stmt->type, $item->name->toString(), $item->getAlias()->toString());
                }
            } elseif ($stmt instanceof Stmt\GroupUse) {
                foreach ($stmt->uses as $item) {
                    $type = $stmt->type !== Stmt\Use_::TYPE_UNKNOWN ? $stmt->type : $item->type;
                    $imports->register($type, Name::concat($stmt->prefix, $item->name)->toString(), $item->getAlias()->toString());
                }
            }
        }

        return $imports;
    }

    public static function key(int $type, string $alias): string
    {
        return $type . ':' . strtolower($alias);
    }

    /**
     * Returns the key of the import a name relies on, if any.
     */
    public static function keyOf(Name $name, int $type): ?string
    {
        if ($name->isFullyQualified() || $name->isRelative() || $name->isSpecialClassName()) {
            return null;
        }

        if ($name->isQualified()) {
            return self::key(Stmt\Use_::TYPE_NORMAL, $name->getFirst());
        }

        return self::key($type, $name->getFirst());
    }

    public function resolve(int $type, string $alias): ?string
    {
        return $this->names[self::key($type, $alias)] ?? null;
    }

    public function aliasOf(int $type, string $name): ?string
    {
        return $this->aliases[self::key($type, $name)] ?? null;
    }

    /**
     * Resolves a class name, or a qualified function or constant name, as it would be in the given namespace.
     */
    public function resolveClassName(Name $name, ?string $namespace): string
    {
        if ($name->isFullyQualified()) {
            return $name->toString();
        }

        if (! $name->isRelative()) {
            $import = $this->resolve(Stmt\Use_::TYPE_NORMAL, $name->getFirst());

            if ($import !== null) {
                return $name->isQualified() ? $import . '\\' . $name->slice(1)?->toString() : $import;
            }
        }

        return ltrim($namespace . '\\' . $name->toString(), '\\');
    }

    /**
     * @param Stmt\Use_::TYPE_* $type
     */
    public function add(int $type, string $name, ?string $alias = null): void
    {
        $alias ??= Names::lastSegment($name);

        $this->register($type, $name, $alias);
        $this->pending[self::key($type, $name)] = [$type, $name, $alias];
    }

    /**
     * Inserts the imports that were added into the given statements.
     *
     * @param Stmt[] $stmts
     */
    public function insertInto(array &$stmts): void
    {
        $pending = $this->pending;
        $this->pending = [];

        uasort($pending, static fn (array $a, array $b) => strcasecmp($a[1], $b[1]));

        foreach ($pending as [$type, $name, $alias]) {
            $item = new UseItem(new Name($name), $alias !== Names::lastSegment($name) ? new Identifier($alias) : null);

            if ($this->updateExistingImport($stmts, $type, $name, $item)) {
                continue;
            }

            self::insertImport($stmts, $type, new Stmt\Use_([$item], $type));
        }
    }

    /**
     * @param Stmt[] $stmts
     * @param array<string, true> $used
     */
    public static function hasUnused(array $stmts, array $used): bool
    {
        foreach ($stmts as $stmt) {
            if (! $stmt instanceof Stmt\Use_) {
                continue;
            }

            if (array_any($stmt->uses, static fn (UseItem $item) => ! isset($used[self::key($stmt->type, $item->getAlias()->toString())]))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes the imports that were relied on before, but no longer are.
     * Imports that are mentioned in comments are kept, since doc comments may refer to them.
     *
     * @param Stmt[] $stmts
     * @param array<string, true> $previouslyUsed
     * @param array<string, true> $used
     */
    public static function removeUnused(array &$stmts, array $previouslyUsed, array $used, string $comments): void
    {
        foreach ($stmts as $index => $stmt) {
            if (! $stmt instanceof Stmt\Use_) {
                continue;
            }

            $items = array_values(array_filter(
                $stmt->uses,
                static function (UseItem $item) use ($stmt, $previouslyUsed, $used, $comments): bool {
                    $alias = $item->getAlias()->toString();
                    $key = self::key($stmt->type, $alias);

                    if (! isset($previouslyUsed[$key]) || isset($used[$key])) {
                        return true;
                    }

                    return preg_match('/(?<![\\w\\\\$])' . preg_quote($alias, '/') . '(?!\\w)/i', $comments) === 1;
                },
            ));

            if ($items === []) {
                unset($stmts[$index]);
            } elseif (count($items) !== count($stmt->uses)) {
                $stmt->uses = $items;
            }
        }

        $stmts = array_values($stmts);
    }

    private function register(int $type, string $name, string $alias): void
    {
        $this->names[self::key($type, $alias)] = $name;
        $this->aliases[self::key($type, $name)] = $alias;
    }

    /**
     * @param Stmt[] $stmts
     */
    private function updateExistingImport(array $stmts, int $type, string $name, UseItem $replacement): bool
    {
        foreach ($stmts as $stmt) {
            $items = match (true) {
                $stmt instanceof Stmt\Use_ && $stmt->type === $type => $stmt->uses,
                $stmt instanceof Stmt\GroupUse => array_filter(
                    $stmt->uses,
                    static fn (UseItem $item) => ($stmt->type !== Stmt\Use_::TYPE_UNKNOWN ? $stmt->type : $item->type) === $type,
                ),
                default => [],
            };

            foreach ($items as $item) {
                $itemName = $stmt instanceof Stmt\GroupUse ? Name::concat($stmt->prefix, $item->name) : $item->name;

                if (strcasecmp($itemName->toString(), $name) !== 0) {
                    continue;
                }

                if ($item->getAlias()->toString() !== $replacement->getAlias()->toString()) {
                    $item->alias = $replacement->alias;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param Stmt[] $stmts
     */
    private static function insertImport(array &$stmts, int $type, Stmt\Use_ $import): void
    {
        $name = $import->uses[0]->name->toString();
        $lastImportBefore = null;
        $lastImportType = null;

        foreach ($stmts as $index => $stmt) {
            if (! $stmt instanceof Stmt\Use_ && ! $stmt instanceof Stmt\GroupUse && ! $stmt instanceof Stmt\Declare_ && ! Printer::isBlankLine($stmt)) {
                break;
            }

            if (! $stmt instanceof Stmt\Use_) {
                continue;
            }

            if ($stmt->type === $type) {
                if (strcasecmp($stmt->uses[0]->name->toString(), $name) > 0) {
                    array_splice($stmts, $index, 0, [$import]);

                    return;
                }

                $lastImportBefore = $index;
                $lastImportType = $stmt->type;
            } elseif ($stmt->type < $type) {
                $lastImportBefore = $index;
                $lastImportType = $stmt->type;
            } elseif ($lastImportBefore === null || $lastImportType !== $type) {
                $position = $lastImportBefore !== null ? $lastImportBefore + 1 : $index;
                $replacement = $lastImportBefore !== null ? [Printer::blankLine(), $import] : [$import, Printer::blankLine()];

                array_splice($stmts, $position, 0, $replacement);

                return;
            } else {
                array_splice($stmts, $lastImportBefore + 1, 0, [$import]);

                return;
            }
        }

        if ($lastImportBefore !== null) {
            array_splice($stmts, $lastImportBefore + 1, 0, $lastImportType === $type ? [$import] : [Printer::blankLine(), $import]);

            return;
        }

        $position = 0;

        while (isset($stmts[$position]) && $stmts[$position] instanceof Stmt\Declare_) {
            $position++;
        }

        array_splice($stmts, $position, 0, $stmts === [] ? [$import] : [$import, Printer::blankLine()]);
    }
}
