<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class UpdateContainerFunctionImportsRector extends AbstractRector
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
            if ($node->name->toString() === 'Tempest\get') {
                $node->name = new Node\Name('Tempest\Container\get');
            }

            if ($node->name->toString() === 'Tempest\invoke') {
                $node->name = new Node\Name('Tempest\Container\invoke');
            }

            return null;
        }

        if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
            $functionName = $node->name->toString();

            if ($functionName === 'Tempest\get') {
                $node->name = new Node\Name\FullyQualified('Tempest\Container\get');

                return null;
            }

            if ($functionName === 'Tempest\invoke') {
                $node->name = new Node\Name\FullyQualified('Tempest\Container\invoke');

                return null;
            }
        }

        return null;
    }
}
