<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class UpdateViewFunctionImportsRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            Node\UseItem::class,
            Node\Expr\FuncCall::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if ($node instanceof Node\UseItem) {
            if ($node->name->toString() === 'Tempest\view') {
                $node->name = new Node\Name('Tempest\View\view');
            }

            return null;
        }

        return null;
    }
}
