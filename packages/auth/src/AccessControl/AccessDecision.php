<?php

namespace Tempest\Auth\AccessControl;

final class AccessDecision
{
    /**
     * @param bool $granted Whether access is granted.
     * @param null|string $message An optional message or translation key explaining the decision.
     */
    public function __construct(
        public readonly bool $granted,
        public readonly ?string $message = null,
    ) {}

    /**
     * Determines if access is granted or denied based on the provided decision.
     */
    public static function from(null|self|bool $decision): self
    {
        if ($decision instanceof self) {
            return $decision;
        }

        return new self(granted: (bool) $decision);
    }

    /** 
     * Grants access to the resource.
     */
    public static function granted(): self
    {
        return new self(granted: true);
    }

    /**
     * Denies access to the resource with an optional message.
     * 
     * @param null|string $message An optional message or translation key explaining the decision.
     */
    public static function denied(?string $message = null): self
    {
        return new self(granted: false, message: $message);
    }
}
