<?php

namespace Tempest\Upgrade\Tempest320;

use BadMethodCallException;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Rector\AbstractRector;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;

final class UpdateSessionIdResolverImplementationsRector extends AbstractRector
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

        if (! array_any($node->implements, fn (Name $name) => $this->isName($name, SessionIdResolver::class))) {
            return null;
        }

        if ($node->getMethod('issueNewId') instanceof ClassMethod) {
            return null;
        }

        $node->stmts[] = $this->createIssueNewIdMethod();

        return $node;
    }

    private function createIssueNewIdMethod(): ClassMethod
    {
        $method = $this->nodeFactory->createPublicMethod('issueNewId');

        $method->returnType = new FullyQualified(SessionId::class);
        $method->stmts = [
            new Expression(new Throw_(new New_(
                new FullyQualified(BadMethodCallException::class),
                $this->nodeFactory->createArgs(['issueNewId() is not implemented yet.']),
            ))),
        ];

        return $method;
    }
}
