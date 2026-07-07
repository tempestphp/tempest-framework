<?php

namespace Tempest\Upgrade\Tempest314;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Rector\AbstractRector;
use Tempest\Core\Kernel;

final class UpdateKernelImplementationsRector extends AbstractRector
{
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

        if (! $this->implementsKernel($node)) {
            return null;
        }

        $hasChanged = false;
        $shutdown = $node->getMethod('shutdown');

        if ($shutdown instanceof ClassMethod && ! $this->isVoidReturnType($shutdown)) {
            $shutdown->returnType = new Identifier('void');
            $this->removeReturnValues($shutdown);
            $hasChanged = true;
        }

        return $hasChanged ? $node : null;
    }

    private function implementsKernel(Class_ $class): bool
    {
        return array_any(
            $class->implements,
            fn (Name $name) => $this->isInterfaceName($name, Kernel::class, 'Kernel'),
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

    private function isVoidReturnType(ClassMethod $method): bool
    {
        return $method->returnType instanceof Identifier && $method->returnType->toString() === 'void';
    }

    private function removeReturnValues(ClassMethod $method): void
    {
        if ($method->stmts === null) {
            return;
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class extends NodeVisitorAbstract {
            public function leaveNode(Node $node): ?array
            {
                if (! $node instanceof Return_ || ! $node->expr instanceof Expr) {
                    return null;
                }

                return [
                    new Expression($node->expr),
                    new Return_(),
                ];
            }
        });

        $method->stmts = array_filter(
            $traverser->traverse($method->stmts),
            static fn (Node $node): bool => $node instanceof Stmt,
        );
    }
}
