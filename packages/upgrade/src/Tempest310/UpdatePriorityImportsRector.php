<?php

namespace Tempest\Upgrade\Tempest310;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\UseItem;
use Rector\Rector\AbstractRector;

final class UpdatePriorityImportsRector extends AbstractRector
{
    private const string OLD_CLASS = 'Tempest\Core\Priority';

    private const string NEW_CLASS = 'Tempest\Support\Priority';

    public function getNodeTypes(): array
    {
        return [
            UseItem::class,
            FullyQualified::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof UseItem) {
            if ($node->name->toString() !== self::OLD_CLASS) {
                return null;
            }

            $node->name = new Name(self::NEW_CLASS);

            return $node;
        }

        if ($node instanceof FullyQualified && $node->toString() === self::OLD_CLASS) {
            return new FullyQualified(self::NEW_CLASS);
        }

        return null;
    }
}
