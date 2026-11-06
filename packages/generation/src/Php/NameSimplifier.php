<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

/**
 * Makes sure every name resolves correctly in its namespace, importing fully qualified names where possible.
 * Names are only changed after all of them were visited, so new imports never change what another name refers to.
 *
 * @internal
 */
final class NameSimplifier
{
    private const int IGNORED = 0;

    /** @var array<string, true> */
    public array $usedImports = [];

    /** @var array<string, string> short names that refer to a class without an import, mapped to that class */
    private array $reservedClasses = [];

    /** @var array<string, true> short names that refer to a function without an import */
    private array $reservedFunctions = [];

    /** @var list<array{Name, int, bool, bool, ?MethodBody}> */
    private array $names = [];

    /** @var array<int, Node> */
    private array $parents = [];

    /**
     * @param array<string, true> $declaredClasses lowercased names of the classes declared in the namespace
     */
    public function __construct(
        private readonly ?string $namespace,
        private readonly Imports $imports,
        private readonly array $declaredClasses,
        private readonly bool $simplifyImplements,
        private readonly bool $simplifyBodies,
    ) {}

    /**
     * @param Stmt[] $stmts
     */
    public function simplify(array $stmts): void
    {
        foreach (array_keys($this->declaredClasses) as $class) {
            $this->reservedClasses[$class] = ltrim($this->namespace . '\\' . $class, '\\');
        }

        foreach ($stmts as $stmt) {
            $this->visit($stmt, null, Stmt\Use_::TYPE_NORMAL, inBody: false, inImplements: false, methodBody: null);
        }

        foreach ($this->names as [$name, $type, $inBody, $inImplements, $methodBody]) {
            $simplified = match ($type) {
                Stmt\Use_::TYPE_NORMAL => $this->simplifyClassName($name, $inBody, $inImplements),
                Stmt\Use_::TYPE_FUNCTION => $this->simplifyFunctionName($name, $inBody),
                default => $this->qualifyBrokenName($name, $type),
            };

            if ($simplified !== null) {
                $this->replace($name, $simplified);
                $methodBody?->replaceName($name, $simplified);
            }

            $key = Imports::keyOf($simplified ?? $name, $type);

            if ($key !== null) {
                $this->usedImports[$key] = true;
            }
        }
    }

    /**
     * Collects every name along with the context it is used in. Import statements are skipped.
     *
     * @param int $nameType The `Stmt\Use_::TYPE_*` of a name visited directly, or `IGNORED`.
     */
    private function visit(Node $node, ?Node $parent, int $nameType, bool $inBody, bool $inImplements, ?MethodBody $methodBody): void
    {
        if ($parent !== null) {
            $this->parents[spl_object_id($node)] = $parent;
        }

        if ($node instanceof Name) {
            if ($nameType !== self::IGNORED) {
                $this->reserve($node, $nameType);
                $this->names[] = [$node, $nameType, $inBody, $inImplements, $methodBody];
            }

            return;
        }

        if ($node instanceof Stmt\Use_ || $node instanceof Stmt\GroupUse) {
            return;
        }

        if ($node instanceof Stmt\Function_) {
            $this->reservedFunctions[$node->name->toLowerString()] = true;
        }

        $childNameType = match (true) {
            $node instanceof Stmt\Namespace_ => self::IGNORED,
            $node instanceof Expr\FuncCall => Stmt\Use_::TYPE_FUNCTION,
            $node instanceof Expr\ConstFetch => Stmt\Use_::TYPE_CONSTANT,
            default => Stmt\Use_::TYPE_NORMAL,
        };
        $hasBody = $node instanceof Stmt\ClassMethod || $node instanceof Stmt\Function_;
        $bodyMethodBody = $node instanceof Stmt\ClassMethod ? $node->getAttribute(MethodBody::ATTRIBUTE) ?? $methodBody : $methodBody;

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $value = $node->{$subNodeName};
            $isBody = $hasBody && $subNodeName === 'stmts';
            $childInBody = $inBody || $isBody;
            $childMethodBody = $isBody ? $bodyMethodBody : $methodBody;
            $childInImplements = $node instanceof Stmt\Class_ && $subNodeName === 'implements';

            foreach (is_array($value) ? $value : [$value] as $child) {
                if (! $child instanceof Node) {
                    continue;
                }

                $this->visit($child, $node, $childNameType, $childInBody, $childInImplements, $childMethodBody);
            }
        }
    }

    /**
     * Remembers the short names that already refer to something without being imported.
     */
    private function reserve(Name $name, int $type): void
    {
        if ($name->isFullyQualified() || $name->isRelative() || $name->isSpecialClassName()) {
            return;
        }

        $first = $name->getFirst();

        if ($type !== Stmt\Use_::TYPE_NORMAL && $name->isUnqualified()) {
            if ($type === Stmt\Use_::TYPE_FUNCTION && $this->imports->resolve($type, $first) === null) {
                $this->reservedFunctions[strtolower($first)] = true;
            }

            return;
        }

        if ($this->imports->resolve(Stmt\Use_::TYPE_NORMAL, $first) !== null) {
            return;
        }

        $resolved = Names::resolve($name);

        if ($resolved !== null && strcasecmp($resolved, $this->imports->resolveClassName($name, $this->namespace)) !== 0) {
            return;
        }

        $this->reservedClasses[strtolower($first)] = ltrim($this->namespace . '\\' . $first, '\\');
    }

    private function replace(Name $name, Name $replacement): void
    {
        $replacement->setAttribute('startFilePos', $name->getStartFilePos());
        $replacement->setAttribute('endFilePos', $name->getEndFilePos());

        $parent = $this->parents[spl_object_id($name)] ?? null;

        if ($parent === null) {
            return;
        }

        foreach ($parent->getSubNodeNames() as $subNodeName) {
            $value = $parent->{$subNodeName};

            if ($value === $name) {
                $parent->{$subNodeName} = $replacement;

                break;
            }

            if (is_array($value) && ($key = array_search($name, $value, strict: true)) !== false) {
                $value[$key] = $replacement;
                $parent->{$subNodeName} = $value;

                break;
            }
        }

        $node = $parent;

        while ($node !== null && Changes::mark($node)) {
            $node = $this->parents[spl_object_id($node)] ?? null;
        }
    }

    private function simplifyClassName(Name $name, bool $inBody, bool $inImplements): ?Name
    {
        if ($name->isSpecialClassName()) {
            return null;
        }

        $fullyQualifiedName = Names::resolve($name);

        if ($fullyQualifiedName === null) {
            return null;
        }

        $resolvesCorrectly = strcasecmp($this->imports->resolveClassName($name, $this->namespace), $fullyQualifiedName) === 0;
        $shouldSimplify = $name->isFullyQualified()
        && match (true) {
            $inBody => $this->simplifyBodies,
            $inImplements => $this->simplifyImplements,
            default => true,
        };

        if ($resolvesCorrectly && ! $shouldSimplify) {
            return null;
        }

        $alias = $this->importClass($fullyQualifiedName);

        if ($alias === null) {
            return $name->isFullyQualified() ? null : new Name\FullyQualified($fullyQualifiedName);
        }

        return new Name($alias, [Names::RESOLVED_NAME_ATTRIBUTE => new Name\FullyQualified($fullyQualifiedName)]);
    }

    private function simplifyFunctionName(Name $name, bool $inBody): ?Name
    {
        if (! $name->isFullyQualified()) {
            return $this->qualifyBrokenName($name, Stmt\Use_::TYPE_FUNCTION);
        }

        if ($inBody && ! $this->simplifyBodies) {
            return null;
        }

        $fullyQualifiedName = $name->toString();

        if (Names::namespaceOf($fullyQualifiedName) === '') {
            return null;
        }

        $alias = $this->imports->aliasOf(Stmt\Use_::TYPE_FUNCTION, $fullyQualifiedName);

        if ($alias === null) {
            $alias = Names::lastSegment($fullyQualifiedName);

            if ($this->imports->resolve(Stmt\Use_::TYPE_FUNCTION, $alias) !== null || isset($this->reservedFunctions[strtolower($alias)])) {
                return null;
            }

            $this->imports->add(Stmt\Use_::TYPE_FUNCTION, $fullyQualifiedName);
        }

        return new Name($alias);
    }

    /**
     * Fully qualifies a function or constant name that no longer refers to the same thing, for example after the namespace changed.
     */
    private function qualifyBrokenName(Name $name, int $type): ?Name
    {
        $fullyQualifiedName = $name->isFullyQualified() ? null : Names::resolve($name);

        if ($fullyQualifiedName === null) {
            return null;
        }

        $resolvedName = $name->isUnqualified()
            ? $this->imports->resolve($type, $name->toString())
            : $this->imports->resolveClassName($name, $this->namespace);

        if ($resolvedName !== null && strcasecmp($resolvedName, $fullyQualifiedName) === 0) {
            return null;
        }

        return new Name\FullyQualified($fullyQualifiedName);
    }

    /**
     * Returns the name a class can be referred to with, importing it when needed.
     */
    private function importClass(string $fullyQualifiedName): ?string
    {
        $alias = $this->imports->aliasOf(Stmt\Use_::TYPE_NORMAL, $fullyQualifiedName);

        if ($alias !== null) {
            return $alias;
        }

        $shortName = Names::lastSegment($fullyQualifiedName);

        if ($this->imports->resolve(Stmt\Use_::TYPE_NORMAL, $shortName) !== null) {
            return null;
        }

        if (strcasecmp(Names::namespaceOf($fullyQualifiedName), $this->namespace ?? '') === 0) {
            $this->reservedClasses[strtolower($shortName)] = $fullyQualifiedName;

            return $shortName;
        }

        $reservedClass = $this->reservedClasses[strtolower($shortName)] ?? null;

        if ($reservedClass !== null && strcasecmp($reservedClass, $fullyQualifiedName) !== 0) {
            return null;
        }

        $this->imports->add(Stmt\Use_::TYPE_NORMAL, $fullyQualifiedName);

        return $shortName;
    }
}
