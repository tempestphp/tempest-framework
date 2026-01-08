<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class UpdateCommandFunctionImportsRector extends AbstractRector
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
            if ($node->name->toString() === 'Tempest\command') {
                $node->name = new Node\Name('Tempest\CommandBus\command');
            }

            return null;
        }

        if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
            $functionName = $node->name->toString();

            if ($functionName === 'Tempest\command') {
                $node->name = new Node\Name\FullyQualified('Tempest\CommandBus\command');

                return null;
            }
        }

        return null;
    }
}
