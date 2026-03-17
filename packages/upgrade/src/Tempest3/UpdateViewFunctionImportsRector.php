<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\UseItem;
use Rector\Rector\AbstractRector;

final class UpdateViewFunctionImportsRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            UseItem::class,
            FuncCall::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if ($node instanceof UseItem) {
            if ($node->name->toString() === 'Tempest\view') {
                $node->name = new Name('Tempest\View\view');
            }

            return null;
        }

        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();

            if ($functionName === 'Tempest\view') {
                $node->name = new FullyQualified('Tempest\View\view');

                return null;
            }
        }

        return null;
    }
}
