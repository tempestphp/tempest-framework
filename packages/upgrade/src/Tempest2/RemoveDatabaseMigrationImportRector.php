<?php

namespace Tempest\Upgrade\Tempest2;

use PhpParser\Node;
use PhpParser\Node\UseItem;
use PhpParser\NodeVisitor;
use Rector\Rector\AbstractRector;

final class RemoveDatabaseMigrationImportRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            UseItem::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if (! $node instanceof UseItem) {
            return null;
        }

        if ($node->name->toString() === 'Tempest\Database\DatabaseMigration') {
            return NodeVisitor::REMOVE_NODE;
        }

        return null;
    }
}
