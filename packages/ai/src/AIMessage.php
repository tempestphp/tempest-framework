<?php

declare(strict_types=1);

namespace Tempest\AI;

final readonly class AIMessage
{
    public function __construct(
        public MessageRole $role,
        public string $content,
    ) {}

    public static function system(string $content): self
    {
        return new self(MessageRole::SYSTEM, $content);
    }

    public static function user(string $content): self
    {
        return new self(MessageRole::USER, $content);
    }

    public static function assistant(string $content): self
    {
        return new self(MessageRole::ASSISTANT, $content);
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role->value,
            'content' => $this->content,
        ];
    }
}
