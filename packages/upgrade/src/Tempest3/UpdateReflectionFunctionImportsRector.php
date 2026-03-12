<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\UseItem;
use Rector\Rector\AbstractRector;

final class UpdateReflectionFunctionImportsRector extends AbstractRector
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
            if ($node->name->toString() === 'Tempest\reflect') {
                $node->name = new Name('Tempest\Reflection\reflect');
            }

            return null;
        }

        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();

            if ($functionName === 'Tempest\reflect') {
                $node->name = new FullyQualified('Tempest\Reflection\reflect');

                return null;
            }
        }

        return null;
    }
}
