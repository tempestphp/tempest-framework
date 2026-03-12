<?php

namespace Tempest\Upgrade\Tempest2;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\UseItem;
use Rector\Rector\AbstractRector;

final class UpdateUriImportsRector extends AbstractRector
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

        if ($node->name->toString() === 'Tempest\uri') {
            $node->name = new Name('Tempest\Router\uri');
        }

        if ($node->name->toString() === 'Tempest\is_current_uri') {
            $node->name = new Name('Tempest\Router\is_current_uri');
        }

        return null;
    }
}
