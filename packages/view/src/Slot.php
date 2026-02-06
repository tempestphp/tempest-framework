<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\Reflection\ClassReflector;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\View\Export\ExportableViewObject;
use Tempest\View\Parser\Token;

final class Slot implements ExportableViewObject
{
    public const string DEFAULT = 'default';

    public function __construct(
        public string $name,
        public array $attributes,
        string $content,
    ) {
        $this->content = base64_encode($content);
    }

    public string $content {
        get => ($d = base64_decode($this->content, strict: true)) === false ? '' : $d;
    }

    public ImmutableArray $exportData {
        get => new ImmutableArray([
            'name' => $this->name,
            'attributes' => $this->attributes,
            'content' => base64_encode($this->content),
        ]);
    }

    public static function restore(mixed ...$data): ExportableViewObject
    {
        $self = new ClassReflector(self::class)->newInstanceWithoutConstructor();

        $self->name = $data['name'];
        $self->attributes = $data['attributes'];
        $self->content = $data['content'];

        return $self;
    }

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
}
