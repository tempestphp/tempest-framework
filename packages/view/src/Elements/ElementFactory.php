<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\Core\Environment;
use Tempest\View\Attributes\PhpAttribute;
use Tempest\View\Element;
use Tempest\View\Parser\TempestViewCompiler;
use Tempest\View\Parser\Token;
use Tempest\View\Parser\TokenType;
use Tempest\View\Slot;
use Tempest\View\ViewCache;
use Tempest\View\ViewConfig;

final class ElementFactory
{
    private TempestViewCompiler $compiler;

    private(set) bool $isHtml = false;

    public function __construct(
        private readonly ViewConfig $viewConfig,
        private readonly Environment $environment,
        private readonly ViewCache $viewCache,
    ) {}

    public function setViewCompiler(TempestViewCompiler $compiler): self
    {
        $this->compiler = $compiler;

        return $this;
    }

    public function withIsHtml(bool $isHtml): self
    {
        $clone = $this->clone();

        $clone->isHtml = $isHtml;

        return $clone;
    }

    public function make(Token $token, Element $parent): ?Element
    {
        if (
            $token->type === TokenType::OPEN_TAG_END
            || $token->type === TokenType::ATTRIBUTE_NAME
            || $token->type === TokenType::ATTRIBUTE_VALUE
            || $token->type === TokenType::SELF_CLOSING_TAG_END
        ) {
            return null;
        }

        $attributes = $token->htmlAttributes;

        foreach ($token->phpAttributes as $index => $content) {
            $attributes[] = new PhpAttribute((string) $index, $content);
        }

        if ($token->type === TokenType::CONTENT) {
            $text = $token->compile();

            if (trim($text) === '') {
                return null;
            }

            $element = new TextElement(text: $text);
        } elseif ($token->type === TokenType::WHITESPACE) {
            $element = new WhitespaceElement($token->content);
        } elseif ($token->type !== TokenType::PHP && (! $token->tag || $token->type === TokenType::COMMENT)) {
            $element = new RawElement(
                token: $token,
                tag: null,
                content: $token->compile(),
            );
        } elseif ($token->tag === 'code' || $token->tag === 'pre') {
            $element = new RawElement(
                token: $token,
                tag: $token->tag,
                content: $token->compileChildren(),
                attributes: $attributes,
            );
        } elseif ($token->type === TokenType::PHP) {
            $element = new PhpElement(
                token: $token,
                content: $token->compile(),
            );
        } elseif ($viewComponentClass = $this->viewConfig->viewComponents[$token->tag] ?? null) {
            $element = new ViewComponentElement(
                token: $token,
                environment: $this->environment,
                compiler: $this->compiler,
                viewCache: $this->viewCache,
                viewComponent: $viewComponentClass,
                attributes: $attributes,
            );
        } elseif ($token->tag === 'x-template') {
            $element = new TemplateElement(
                token: $token,
                attributes: $attributes,
            );
        } elseif ($token->tag === 'x-slot') {
            $element = new SlotElement(
                token: $token,
                name: $token->getAttribute('name') ?? Slot::DEFAULT,
                attributes: $attributes,
            );
        } else {
            $element = new GenericElement(
                token: $token,
                tag: $token->tag,
                isHtml: $this->isHtml,
                attributes: $attributes,
            );
        }

        $element->setParent($parent);

        foreach ($token->children as $child) {
            $this->clone()->make(
                token: $child,
                parent: $element,
            );
        }

        return $element;
    }

    private function clone(): self
    {
        return clone $this;
    }
}
