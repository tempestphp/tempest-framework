<?php

namespace Tempest\Upgrade\Tempest2;

use PhpParser\Node;
use PhpParser\NodeVisitor;
use Rector\Rector\AbstractRector;

final class RemoveIdImportRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            Node\UseItem::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if (! ($node instanceof Node\UseItem)) {
            return null;
        }

        if ($node->name->toString() === 'Tempest\\Database\\Id') {
            return NodeVisitor::REMOVE_NODE;
        }

        return null;
    }
}
