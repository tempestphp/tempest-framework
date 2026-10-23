<?php

namespace Tempest\Upgrade\Tempest321;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Tempest\Container\Container;
use Tempest\Container\Lifetime;

final class UpdateContainerImplementationsRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof Class_ || ! $this->implementsContainer($node)) {
            return null;
        }

        $singleton = $node->getMethod('singleton');

        if (! $singleton instanceof ClassMethod || count($singleton->params) !== 3) {
            return null;
        }

        $singleton->params[] = new Param(
            var: new Node\Expr\Variable('lifetime'),
            default: new ConstFetch(new Name('null')),
            type: new NullableType(new Name\FullyQualified(Lifetime::class)),
        );

        return $node;
    }

    private function implementsContainer(Class_ $class): bool
    {
        foreach ($class->implements as $interface) {
            $resolvedName = $interface->getAttribute('resolvedName');

            if ($resolvedName instanceof Name && $resolvedName->toString() === Container::class) {
                return true;
            }

            if ($interface->toString() === Container::class) {
                return true;
            }
        }

        return false;
    }
}
