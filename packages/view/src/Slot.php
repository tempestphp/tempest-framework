<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\View\Parser\Token;

final class Slot
{
    public const string DEFAULT = 'default';

    public function __construct(
        public string $name,
        public array $attributes,
        public string $content,
    ) {}

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public static function named(Token $token): self
    {
        $name = $token->getAttribute('name');
        $attributes = $token->htmlAttributes;
        $content = $token->compileChildren();

        return new self(
            name: $name,
            attributes: $attributes,
            content: $content,
        );
    }

    public static function default(Token ...$tokens): self
    {
        $name = Slot::DEFAULT;
        $attributes = [];
        $content = '';

        foreach ($tokens as $token) {
            $content .= $token->compile();
        }

        return new self(
            name: $name,
            attributes: $attributes,
            content: $content,
        );
    }

    public static function __set_state(array $array): object
    {
        return new self(...$array);
    }
}
