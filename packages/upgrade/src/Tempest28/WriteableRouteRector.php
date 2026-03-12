<?php

namespace Tempest\Upgrade\Tempest28;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Tempest\Router\Route;

final class WriteableRouteRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            Class_::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if (! $node instanceof Class_) {
            return null;
        }

        // Check whether this class implements Tempest\Router\Route
        $implements = $node->implements;

        $implementsRoute = array_find_key(
            $implements,
            static fn (Name $name) => $name->toString() === Route::class,
        );

        if ($implementsRoute === null) {
            return null;
        }

        if (! $node->isReadonly()) {
            return null;
        }

        $node->flags &= ~Modifiers::READONLY;

        return null;
    }
}
