<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;

final readonly class PhpMethod
{
    /**
     * @internal
     */
    public function __construct(
        private Stmt\ClassMethod $node,
    ) {}

    public function getName(): string
    {
        return $this->node->name->toString();
    }

    public function setReturnType(?string $type): self
    {
        $this->node->returnType = $type !== null ? TypeParser::parse($type) : null;

        return $this;
    }

    public function addParameter(string $name, ?string $type = null): self
    {
        $this->node->params[] = new Param(
            var: new Variable(ltrim($name, '$')),
            type: $type !== null ? TypeParser::parse($type) : null,
        );

        return $this;
    }

    public function setBody(string $code): self
    {
        [$body, $stmts] = MethodBody::parse($code);

        $this->node->stmts = $stmts;
        $this->node->setAttribute(MethodBody::ATTRIBUTE, $body);
        $this->node->setAttribute('origNode', null);

        return $this;
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public function addAttribute(string $name, array $arguments = []): self
    {
        Attributes::add($this->node, $name, $arguments);

        return $this;
    }

    public function removeAttribute(string $name): self
    {
        Attributes::remove($this->node, $name);

        return $this;
    }
}
