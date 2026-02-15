<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class UpdateHasContextRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            Node\UseItem::class,
            Node\Stmt\Class_::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if ($node instanceof Node\UseItem) {
            $name = $node->name->toString();

            if ($name === 'Tempest\Core\HasContext' || $name === 'HasContext') {
                $node->name = new Node\Name('Tempest\Core\ProvidesContext');
            }

            return null;
        }

        if (! $node instanceof Node\Stmt\Class_) {
            return null;
        }

        $implements = $node->implements;

        $implementsHasContext = array_find_key(
            array: $implements,
            callback: static fn (Node\Name $name) => $name->toString() === 'Tempest\Core\HasContext' || $name->toString() === 'HasContext',
        );

        if ($implementsHasContext === null) {
            return null;
        }

        $implements[$implementsHasContext] = new Node\Name('\Tempest\Core\ProvidesContext');
        $node->implements = $implements;

        return null;
    }
}
