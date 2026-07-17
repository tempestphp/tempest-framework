<?php

namespace Tempest\Upgrade\Tempest314;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Tempest\Database\Connection\Connection;

final class UpdateConnectionImplementationsRector extends AbstractRector
{
    private const array METHODS = [
        'inTransaction' => 'bool',
        'ping' => 'bool',
        'reconnect' => 'void',
    ];

    public function getNodeTypes(): array
    {
        return [
            Class_::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof Class_) {
            return null;
        }

        if (! $this->implementsConnection($node)) {
            return null;
        }

        $hasChanged = false;

        foreach (self::METHODS as $methodName => $returnType) {
            if ($node->getMethod($methodName) instanceof ClassMethod) {
                continue;
            }

            $node->stmts[] = $this->createMethod($methodName, $returnType);
            $hasChanged = true;
        }

        return $hasChanged ? $node : null;
    }

    private function implementsConnection(Class_ $class): bool
    {
        return array_any(
            $class->implements,
            fn (Name $name) => $this->isInterfaceName($name, Connection::class, 'Connection'),
        );
    }

    private function isInterfaceName(Name $name, string $interfaceName, string $shortName): bool
    {
        $names = [
            ltrim($name->toString(), '\\'),
        ];

        $resolvedName = $name->getAttribute('resolvedName');

        if ($resolvedName instanceof Name) {
            $names[] = ltrim($resolvedName->toString(), '\\');
        }

        return array_any(
            $names,
            static fn (string $name) => in_array($name, [$interfaceName, $shortName], strict: true),
        );
    }

    private function createMethod(string $methodName, string $returnType): ClassMethod
    {
        $statements = $returnType === 'bool'
            ? [new Return_(new ConstFetch(new Name('false')))]
            : [];

        return new ClassMethod($methodName, [
            'flags' => Modifiers::PUBLIC,
            'returnType' => new Identifier($returnType),
            'stmts' => $statements,
        ]);
    }
}
