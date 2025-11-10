<?php

namespace Tempest\Upgrade\Tempest28;

use PhpParser\Modifiers;
use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Tempest\Router\Route;

final class WriteableRouteRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            Node\Stmt\Class_::class,
        ];
    }

    public function refactor(Node $node): void
    {
        if (! $node instanceof Node\Stmt\Class_) {
            return;
        }

        // Check whether this class implements Tempest\Router\Route
        $implements = $node->implements;

        $implementsRoute = array_find_key(
            $implements,
            static fn (Node\Name $name) => $name->toString() === Route::class,
        );

        if ($implementsRoute === null) {
            return;
        }

        if (! $node->isReadonly()) {
            return;
        }

        $node->flags &= ~Modifiers::READONLY;
    }
}
