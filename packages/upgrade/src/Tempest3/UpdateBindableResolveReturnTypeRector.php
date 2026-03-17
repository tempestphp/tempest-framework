<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use Rector\Rector\AbstractRector;

final class UpdateBindableResolveReturnTypeRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            Class_::class,
            Interface_::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if ($node instanceof Interface_) {
            if (! $this->hasBindableName($node->extends)) {
                return null;
            }

            $this->refactorMethods($node->getMethods());

            return null;
        }

        if (! $node instanceof Class_) {
            return null;
        }

        if (! $this->hasBindableName($node->implements)) {
            return null;
        }

        $this->refactorMethods($node->getMethods());

        return null;
    }

    /**
     * @param Name[] $names
     */
    private function hasBindableName(array $names): bool
    {
        foreach ($names as $name) {
            $value = ltrim($name->toString(), '\\');

            if ($value === 'Tempest\\Router\\Bindable' || $value === 'Bindable') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ClassMethod[] $methods
     */
    private function refactorMethods(array $methods): void
    {
        foreach ($methods as $method) {
            if ($method->name->toString() !== 'resolve') {
                continue;
            }

            if (! $method->isStatic()) {
                continue;
            }

            if ($method->returnType instanceof NullableType && $method->returnType->type instanceof Identifier && $method->returnType->type->toString() === 'static') {
                continue;
            }

            $method->returnType = new NullableType(new Identifier('static'));
        }
    }
}
