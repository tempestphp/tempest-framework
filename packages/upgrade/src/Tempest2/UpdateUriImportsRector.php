<?php

namespace Tempest\Upgrade\Tempest2;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class UpdateUriImportsRector extends AbstractRector
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

        if ($node->name->toString() === 'Tempest\uri') {
            $node->name = new Node\Name('Tempest\Router\uri');
        }

        if ($node->name->toString() === 'Tempest\is_current_uri') {
            $node->name = new Node\Name('Tempest\Router\is_current_uri');
        }

        return null;
    }
}
