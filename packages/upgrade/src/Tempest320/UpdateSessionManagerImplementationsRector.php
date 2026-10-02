<?php

namespace Tempest\Upgrade\Tempest320;

use BadMethodCallException;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionManager;

final class UpdateSessionManagerImplementationsRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            Class_::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof Class_) {
            return null;
        }

        if (! array_any($node->implements, fn (Name $name) => $this->isName($name, SessionManager::class))) {
            return null;
        }

        if ($node->getMethod('regenerate') instanceof ClassMethod) {
            return null;
        }

        $node->stmts[] = $this->createRegenerateMethod();

        return $node;
    }

    private function createRegenerateMethod(): ClassMethod
    {
        $method = $this->nodeFactory->createPublicMethod('regenerate');

        $method->params[] = $this->nodeFactory->createParamFromNameAndType('session', new ObjectType(Session::class));
        $method->returnType = new Identifier('void');
        $method->stmts = [
            new Expression(new Throw_(new New_(
                new FullyQualified(BadMethodCallException::class),
                $this->nodeFactory->createArgs(['regenerate() is not implemented yet.']),
            ))),
        ];

        return $method;
    }
}
