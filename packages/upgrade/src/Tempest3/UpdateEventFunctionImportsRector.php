<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class UpdateEventFunctionImportsRector extends AbstractRector
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
            if ($node->name->toString() === 'Tempest\event') {
                $node->name = new Node\Name('Tempest\EventBus\event');
            }

            if ($node->name->toString() === 'Tempest\listen') {
                $node->name = new Node\Name('Tempest\EventBus\listen');
            }

            return null;
        }

        if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
            $functionName = $node->name->toString();

            if ($functionName === 'Tempest\event') {
                $node->name = new Node\Name\FullyQualified('Tempest\EventBus\event');

                return null;
            }

            if ($functionName === 'Tempest\listen') {
                $node->name = new Node\Name\FullyQualified('Tempest\EventBus\listen');

                return null;
            }
        }

        return null;
    }
}
