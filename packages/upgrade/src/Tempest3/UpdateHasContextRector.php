<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\UseItem;
use Rector\Rector\AbstractRector;

final class UpdateHasContextRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            UseItem::class,
            Class_::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if ($node instanceof UseItem) {
            $name = $node->name->toString();

            if ($name === 'Tempest\Core\HasContext' || $name === 'HasContext') {
                $node->name = new Name('Tempest\Core\ProvidesContext');
            }

            return null;
        }

        if (! $node instanceof Class_) {
            return null;
        }

        $implements = $node->implements;

        $implementsHasContext = array_find_key(
            array: $implements,
            callback: static fn (Name $name) => $name->toString() === 'Tempest\Core\HasContext' || $name->toString() === 'HasContext',
        );

        if ($implementsHasContext === null) {
            return null;
        }

        $implements[$implementsHasContext] = new Name('\Tempest\Core\ProvidesContext');
        $node->implements = $implements;

        return null;
    }
}
