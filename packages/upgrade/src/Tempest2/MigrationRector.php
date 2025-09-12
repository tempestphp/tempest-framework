<?php

namespace Tempest\Upgrade\Tempest2;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class MigrationRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            Node\Stmt\Class_::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if (! $node instanceof Node\Stmt\Class_) {
            return null;
        }

        $implements = $node->implements;
ld($implements);
//        if (! in_array('', $implements)) {

//        }

        ld($implements);
    }
}